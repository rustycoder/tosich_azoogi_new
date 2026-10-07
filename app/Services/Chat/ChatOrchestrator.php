<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\LlmFeed;
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
            'public_submit_quote_enquiry' => $leadTool,
        ];

        $this->driver = $this->resolveDriver();
    }

    public static function getActiveAiConfig(): array
    {
        $dbConfig = [];
        try {
            $feed = LlmFeed::query()->where('key', 'ai_chat_config')->first();
            if ($feed && ! empty($feed->content)) {
                $decoded = json_decode((string) $feed->content, true);
                if (is_array($decoded)) {
                    $dbConfig = $decoded;
                }
            }
        } catch (Throwable) {
            // DB not available during early boot or testing
        }

        return [
            'driver' => $dbConfig['driver'] ?? config('services.chat.driver', env('CHAT_LLM_DRIVER', 'gemini')),
            'gemini_api_key' => ! empty($dbConfig['gemini_api_key']) ? $dbConfig['gemini_api_key'] : config('services.gemini.api_key', env('GEMINI_API_KEY')),
            'gemini_model' => ! empty($dbConfig['gemini_model']) ? $dbConfig['gemini_model'] : config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.5-flash')),
            'anthropic_api_key' => ! empty($dbConfig['anthropic_api_key']) ? $dbConfig['anthropic_api_key'] : config('services.anthropic.api_key', env('ANTHROPIC_API_KEY')),
            'anthropic_model' => ! empty($dbConfig['anthropic_model']) ? $dbConfig['anthropic_model'] : config('services.anthropic.chat_model', env('ANTHROPIC_CHAT_MODEL', 'claude-3-5-sonnet-20241022')),
            'openrouter_api_key' => ! empty($dbConfig['openrouter_api_key']) ? $dbConfig['openrouter_api_key'] : config('services.openrouter.api_key', env('OPENROUTER_API_KEY')),
            'openrouter_model' => ! empty($dbConfig['openrouter_model']) ? $dbConfig['openrouter_model'] : config('services.openrouter.model', env('OPENROUTER_MODEL', 'anthropic/claude-3.5-sonnet')),
            'openai_api_key' => ! empty($dbConfig['openai_api_key']) ? $dbConfig['openai_api_key'] : config('services.openai.api_key', env('OPENAI_API_KEY')),
            'openai_model' => ! empty($dbConfig['openai_model']) ? $dbConfig['openai_model'] : config('services.openai.chat_model', env('OPENAI_CHAT_MODEL', 'gpt-4o-mini')),
            'custom_providers' => is_array($dbConfig['custom_providers'] ?? null) ? $dbConfig['custom_providers'] : [],
        ];
    }

    public static function makeDriver(
        string $driverName,
        ?string $model = null,
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?string $type = null
    ): IChatLlmDriver {
        $driverName = strtolower(trim($driverName));
        $config = self::getActiveAiConfig();
        $customProviders = $config['custom_providers'] ?? [];

        // Check if $driverName matches a custom provider registered in DB
        if (isset($customProviders[$driverName])) {
            $cp = $customProviders[$driverName];
            $driverType = $type ?: ($cp['type'] ?? 'openai');
            $resolvedKey = $apiKey ?: ($cp['api_key'] ?? null);
            $resolvedModel = $model ?: ($cp['model'] ?? null);
            $resolvedBaseUrl = $baseUrl ?: ($cp['base_url'] ?? null);

            return match ($driverType) {
                'anthropic' => new AnthropicDriver(apiKey: $resolvedKey, model: $resolvedModel),
                'gemini' => new GeminiDriver(apiKey: $resolvedKey, model: $resolvedModel, baseUrl: $resolvedBaseUrl),
                default => new OpenAiDriver(
                    apiKey: $resolvedKey,
                    model: $resolvedModel ?: 'gpt-4o-mini',
                    baseUrl: $resolvedBaseUrl ?: 'https://api.openai.com/v1'
                ),
            };
        }

        return match ($driverName) {
            'gemini' => new GeminiDriver(
                apiKey: $apiKey ?: ($config['gemini_api_key'] ?? null),
                model: $model ?: ($config['gemini_model'] ?? null),
                baseUrl: $baseUrl,
            ),
            'anthropic' => new AnthropicDriver(
                apiKey: $apiKey ?: ($config['anthropic_api_key'] ?? null),
                model: $model ?: ($config['anthropic_model'] ?? null),
            ),
            'openrouter' => new OpenAiDriver(
                apiKey: $apiKey ?: ($config['openrouter_api_key'] ?? null),
                model: $model ?: ($config['openrouter_model'] ?? null),
                baseUrl: $baseUrl ?: (string) config('services.openrouter.base_url', env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1')),
            ),
            'openai' => new OpenAiDriver(
                apiKey: $apiKey ?: ($config['openai_api_key'] ?? null),
                model: $model ?: ($config['openai_model'] ?? null),
                baseUrl: $baseUrl ?: (string) config('services.openai.base_url', env('OPENAI_BASE_URL', 'https://api.openai.com/v1')),
            ),
            default => app(MockLlmDriver::class),
        };
    }

    protected function resolveDriver(): IChatLlmDriver
    {
        $aiConfig = self::getActiveAiConfig();
        $driverName = strtolower((string) ($aiConfig['driver'] ?? config('services.chat.driver', env('CHAT_LLM_DRIVER', 'auto'))));

        if ($driverName !== 'auto') {
            return self::makeDriver($driverName);
        }

        if (! empty($aiConfig['anthropic_api_key'])) {
            return self::makeDriver('anthropic');
        }

        if (! empty($aiConfig['openrouter_api_key'])) {
            return self::makeDriver('openrouter');
        }

        if (! empty($aiConfig['gemini_api_key'])) {
            return self::makeDriver('gemini');
        }

        if (! empty($aiConfig['openai_api_key'])) {
            return self::makeDriver('openai');
        }

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
                    if (str_contains($toolName, 'lead_enquiry') || str_contains($toolName, 'quote_enquiry')) {
                        if (empty($toolArgs['session_uuid'])) {
                            $toolArgs['session_uuid'] = $session->uuid;
                        }
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

ENQUIRIES & LEAD SUBMISSION (3 DISTINCT TYPES):
You can submit 3 distinct types of enquiries via `public_submit_lead_enquiry`:

1. Contact Enquiry (`enquiry_type: "contact"`):
   - For general inquiries, support, consulting requests, engineering questions, or messages to the Azoogi team.
   - Required information before submitting: Full Name, Email address, and Message/Inquiry. (Company optional).
   - If the user asks to send a contact message or contact Azoogi, ask for their Name, Email, and Message first.

2. Quote Request Enquiry (`enquiry_type: "quote"`):
   - For requesting an official pricing quote on fixtures in their quote cart or specified items.
   - Required information before submitting: Full Name, Email address, Phone number (or contact method), and Project notes.
   - If the user asks to submit a quote request, ensure items are in their quote and ask for their Name, Email, and Phone number.

3. Product Specification Enquiry (`enquiry_type: "product"`):
   - For a single specific product configuration enquiry (e.g. from a product page with specific CCT, finish, beam angle, length, or dimming protocol).
   - Required information before submitting: Product Name/SKU, configured specs, Full Name, Email address, and Project location/details.
   - If the user asks to enquire about a specific product, ask for their preferred configurations, Name, and Email.

IMPORTANT RULE:
NEVER call `public_submit_lead_enquiry` with fake or blank details. Always politely ask the visitor to provide their name, email, and required details before calling the submission tool!
PROMPT;
    }
}
