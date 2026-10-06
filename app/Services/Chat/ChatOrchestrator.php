<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chat\Contracts\IChatLlmDriver;
use App\Services\Chat\Contracts\IChatTool;
use App\Services\Chat\Drivers\AnthropicDriver;
use App\Services\Chat\Drivers\GeminiDriver;
use App\Services\Chat\Drivers\MockLlmDriver;
use App\Services\Chat\Drivers\OpenAiDriver;
use App\Services\Chat\Tools\CustomDatasheetGeneratorTool;
use App\Services\Chat\Tools\ProductDetailsAndDownloadsTool;
use App\Services\Chat\Tools\ProductSearchAndFilterTool;
use App\Services\Chat\Tools\QuoteCartManagerTool;
use App\Services\Chat\Tools\SubmitLeadEnquiryTool;
use App\Services\Contracts\IVisitorOriginService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatOrchestrator
{
    /**
     * @var array<string, IChatTool>
     */
    protected array $tools = [];

    protected IChatLlmDriver $driver;

    public function __construct(
        protected IVisitorOriginService $originService,
        ProductSearchAndFilterTool $searchTool,
        ProductDetailsAndDownloadsTool $detailsTool,
        CustomDatasheetGeneratorTool $datasheetTool,
        QuoteCartManagerTool $quoteTool,
        SubmitLeadEnquiryTool $leadTool,
    ) {
        $this->tools = [
            $searchTool->getName() => $searchTool,
            $detailsTool->getName() => $detailsTool,
            $datasheetTool->getName() => $datasheetTool,
            $quoteTool->getName() => $quoteTool,
            $leadTool->getName() => $leadTool,
        ];

        $this->driver = $this->resolveDriver();
    }

    protected function resolveDriver(): IChatLlmDriver
    {
        $driverName = strtolower((string) config('services.chat.driver', env('CHAT_LLM_DRIVER', 'auto')));

        if ($driverName === 'gemini' || (! empty(env('GEMINI_API_KEY')) && $driverName === 'auto')) {
            return app(GeminiDriver::class);
        }

        if ($driverName === 'openai' || (! empty(env('OPENAI_API_KEY')) && $driverName === 'auto')) {
            return app(OpenAiDriver::class);
        }

        if ($driverName === 'anthropic' || (! empty(env('ANTHROPIC_API_KEY')) && $driverName === 'auto')) {
            return app(AnthropicDriver::class);
        }

        // Fallback mock driver when no keys configured
        return app(MockLlmDriver::class);
    }

    public function getOrCreateSession(?string $uuid = null, ?string $referrerUrl = null): ChatSession
    {
        if (! empty($uuid)) {
            $session = ChatSession::where('uuid', $uuid)->first();
            if ($session) {
                return $session;
            }
        }

        $origin = $this->originService->capture();

        return ChatSession::create([
            'uuid' => $uuid ?: (string) Str::uuid(),
            'ip_address' => $origin['ip_address'] ?? request()->ip(),
            'country' => $origin['country'] ?? null,
            'user_agent' => $origin['user_agent'] ?? request()->userAgent(),
            'referrer_url' => $referrerUrl ?: request()->header('referer'),
            'status' => 'active',
            'messages_count' => 0,
        ]);
    }

    /**
     * @return array{response: string, session_uuid: string, cards: array<int, mixed>, messages: array<int, mixed>}
     */
    public function handleUserMessage(ChatSession $session, string $userText): array
    {
        // 1. Record user message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'content' => $userText,
        ]);

        $session->increment('messages_count');

        // 2. Build conversation history for LLM in strict chronological order
        $recentMessages = $session->messages()
            ->reorder('id', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $history = [];
        foreach ($recentMessages as $msg) {
            $history[] = [
                'role' => $msg->isUser() ? 'user' : 'assistant',
                'content' => $msg->content,
            ];
        }

        $systemPrompt = $this->getSystemPrompt();
        $cardsCollected = [];
        $executedToolCalls = [];
        $executedToolResults = [];
        $totalTokens = 0;

        try {
            // Initial LLM call
            $llmResponse = $this->driver->chat($history, array_values($this->tools), $systemPrompt);
            $totalTokens += $llmResponse['tokens_used'] ?? 0;

            // Handle tool calling loop (max 3 rounds)
            $rounds = 0;
            while (! empty($llmResponse['tool_calls']) && $rounds < 3) {
                $rounds++;
                $toolCallResultsForLlm = [];

                foreach ($llmResponse['tool_calls'] as $tc) {
                    $toolName = $tc['name'];
                    $toolArgs = $tc['arguments'];

                    // Inject session UUID if lead submission
                    if ($toolName === 'public_submit_quote_enquiry' && empty($toolArgs['session_uuid'])) {
                        $toolArgs['session_uuid'] = $session->uuid;
                    }

                    if (isset($this->tools[$toolName])) {
                        $toolOutput = $this->tools[$toolName]->execute($toolArgs);
                        $executedToolCalls[] = [
                            'id' => $tc['id'],
                            'name' => $toolName,
                            'arguments' => $toolArgs,
                        ];
                        $executedToolResults[] = [
                            'id' => $tc['id'],
                            'result' => $toolOutput['result'] ?? [],
                        ];

                        if (! empty($toolOutput['cards'])) {
                            $cardsCollected[] = $toolOutput['cards'];
                        }

                        $toolCallResultsForLlm[] = [
                            'role' => 'tool',
                            'tool_call_id' => $tc['id'],
                            'content' => json_encode($toolOutput['result'] ?? []),
                        ];
                    }
                }

                // Add assistant tool use message & tool responses to history
                if (! empty($llmResponse['raw_message'])) {
                    $history[] = $llmResponse['raw_message'];
                } else {
                    $history[] = [
                        'role' => 'assistant',
                        'content' => $llmResponse['content'] ?? '',
                        'tool_calls' => $llmResponse['tool_calls'],
                    ];
                }

                foreach ($toolCallResultsForLlm as $tr) {
                    $history[] = $tr;
                }

                // Call LLM again with tool results
                $llmResponse = $this->driver->chat($history, array_values($this->tools), $systemPrompt);
                $totalTokens += $llmResponse['tokens_used'] ?? 0;
            }

            $assistantReply = $llmResponse['content'] ?: 'Here are the details you requested.';
        } catch (Throwable $e) {
            Log::error('ChatOrchestrator error: '.$e->getMessage(), ['exception' => $e]);
            $assistantReply = 'I apologize, but I encountered an issue retrieving that information. Please browse our product catalog or contact our sales engineering team directly.';
        }

        // 3. Save assistant message
        $assistantMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'assistant',
            'content' => $assistantReply,
            'tool_calls' => ! empty($executedToolCalls) ? $executedToolCalls : null,
            'tool_results' => ! empty($executedToolResults) ? $executedToolResults : null,
            'cards_payload' => ! empty($cardsCollected) ? $cardsCollected : null,
            'tokens_used' => $totalTokens,
        ]);

        $session->increment('messages_count');

        // Update session summary if it's the first exchange
        if ($session->messages_count <= 2 && empty($session->summary)) {
            $session->update(['summary' => mb_substr($userText, 0, 100)]);
        }

        return [
            'response' => $assistantReply,
            'session_uuid' => $session->uuid,
            'cards' => $cardsCollected,
            'message_id' => $assistantMsg->id,
        ];
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are the expert Azoogi Architectural Lighting & Intelligent Controls AI Assistant.
Azoogi is an Australian architectural lighting manufacturer and smart controls engineering specialist based in Sydney, NSW.

Core Brand Competencies:
- Architectural linear profiles, custom LED strip extrusions, high-CRI downlights, floodlights, and commercial lighting.
- Intelligent control ecosystems: Casambi (BLE Mesh), DALI / DALI-2, MADRIX (Pixel Mapping & DMX), and Silvair.
- Custom length cutting, photometric IES testing, and Australian Standards compliance (AS/NZS).

Your Role & Style:
- Professional, technical, concise, and helpful.
- Always focus on the visitor's latest inquiry. If the visitor asks for a new product category or dimension (e.g. asking for downlights after garden lights), immediately search for the new category and do NOT carry over stale filters (such as old IP ratings or unrelated keywords) from prior turns.
- When visitors ask about products, specs, dimensions, or applications, call `public_search_and_filter_products` or `get_product_details_and_downloads` to provide structured interactive cards.
- When visitors ask for custom datasheets, call `generate_custom_datasheet`.
- When visitors want to add items to their quote or view quote items, call `public_manage_quote_list`.
- When visitors want to submit an enquiry or quote, guide them politely or call `public_submit_quote_enquiry`.
- Keep text concise and friendly, allowing the rich visual cards to display product photos, specs, and downloads.
PROMPT;
    }
}
