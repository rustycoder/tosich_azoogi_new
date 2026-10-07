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
use App\Services\Chat\Tools\SendChatTranscriptToSalesTool;
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
        SendChatTranscriptToSalesTool $transcriptTool,
    ) {
        $this->tools = [
            $searchTool->getName() => $searchTool,
            $detailsTool->getName() => $detailsTool,
            $datasheetTool->getName() => $datasheetTool,
            $quoteTool->getName() => $quoteTool,
            $leadTool->getName() => $leadTool,
            $transcriptTool->getName() => $transcriptTool,
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

    /**
     * @param  array{name?: ?string, email?: ?string, project_name?: ?string, phone?: ?string, company?: ?string}  $leadData
     */
    public function getOrCreateSession(?string $uuid = null, ?string $referrerUrl = null, array $leadData = []): ChatSession
    {
        $session = null;
        if (! empty($uuid)) {
            $session = ChatSession::where('uuid', $uuid)->first();
        }

        if ($session) {
            $updates = [];
            if (! empty($leadData['name']) && empty($session->lead_name)) {
                $updates['lead_name'] = $leadData['name'];
            }
            if (! empty($leadData['email']) && empty($session->lead_email)) {
                $updates['lead_email'] = $leadData['email'];
            }
            if (! empty($leadData['project_name']) && empty($session->project_name)) {
                $updates['project_name'] = $leadData['project_name'];
            }
            if (! empty($leadData['phone']) && empty($session->lead_phone)) {
                $updates['lead_phone'] = $leadData['phone'];
            }
            if (! empty($leadData['company']) && empty($session->lead_company)) {
                $updates['lead_company'] = $leadData['company'];
            }
            if (! empty($updates)) {
                $session->update($updates);
            }

            return $session;
        }

        $origin = $this->originService->capture();

        return ChatSession::create([
            'uuid' => $uuid ?: (string) Str::uuid(),
            'ip_address' => $origin['ip_address'] ?? request()->ip(),
            'country' => $origin['country'] ?? null,
            'user_agent' => $origin['user_agent'] ?? request()->userAgent(),
            'referrer_url' => $referrerUrl ?: request()->header('referer'),
            'lead_name' => $leadData['name'] ?? null,
            'lead_email' => $leadData['email'] ?? null,
            'project_name' => $leadData['project_name'] ?? null,
            'lead_phone' => $leadData['phone'] ?? null,
            'lead_company' => $leadData['company'] ?? null,
            'status' => 'active',
            'messages_count' => 0,
        ]);
    }

    public static function getWidgetBranding(): array
    {
        $defaultChips = [
            ['prompt' => 'Show me outdoor garden lights'],
            ['prompt' => 'I want to explore downlights with dimension Ø82mm x 80mm (H)'],
            ['prompt' => 'Show commercial linear profiles with DALI dimming'],
            ['prompt' => 'How to add products to quote list?'],
            ['prompt' => 'Show my quote list'],
            ['prompt' => 'How do I generate a custom PDF datasheet?'],
        ];

        $branding = [];
        try {
            $feed = LlmFeed::query()->where('key', 'ai_widget_branding')->first();
            if ($feed && ! empty($feed->content)) {
                $decoded = json_decode((string) $feed->content, true);
                if (is_array($decoded)) {
                    $branding = $decoded;
                }
            }
        } catch (Throwable) {
        }

        $chips = [];
        $rawChips = ! empty($branding['starter_chips']) && is_array($branding['starter_chips']) ? $branding['starter_chips'] : $defaultChips;
        foreach ($rawChips as $chip) {
            if (is_string($chip) && trim($chip) !== '') {
                $chips[] = ['prompt' => trim($chip)];
            } elseif (is_array($chip) && (! empty($chip['prompt']) || ! empty($chip['label']))) {
                $promptText = trim((string) ($chip['prompt'] ?? $chip['label'] ?? ''));
                if ($promptText !== '') {
                    $chips[] = [
                        'prompt' => $promptText,
                        'label' => ! empty($chip['label']) ? trim((string) $chip['label']) : $promptText,
                        'icon' => ! empty($chip['icon']) ? trim((string) $chip['icon']) : null,
                    ];
                }
            }
        }

        return [
            'ai_name' => ! empty($branding['ai_name']) ? $branding['ai_name'] : 'Azoogi AI Assistant',
            'ai_avatar' => ! empty($branding['ai_avatar']) ? $branding['ai_avatar'] : 'custom',
            'ai_custom_avatar_url' => $branding['ai_custom_avatar_url'] ?? null,
            'ai_subtitle' => ! empty($branding['ai_subtitle']) ? $branding['ai_subtitle'] : 'Architectural & Smart Controls Specialist',
            'startup_message' => ! empty($branding['startup_message']) ? $branding['startup_message'] : 'Welcome to Azoogi Lighting! How can our architectural engineering team assist with your project specifications, lighting schedules, or quotes today?',
            'lead_greeting_template' => $branding['lead_greeting_template'] ?? null,
            'starter_chips' => ! empty($chips) ? $chips : $defaultChips,
            'intake_enabled' => $branding['intake_enabled'] ?? true,
            'intake_require_project' => $branding['intake_require_project'] ?? false,
            'intake_require_email' => $branding['intake_require_email'] ?? false,
            'intake_require_name' => $branding['intake_require_name'] ?? false,
        ];
    }

    public static function getKnowledgeRules(): array
    {
        $defaultRulesSections = [
            'tone' => 'Target Persona: Professional Trade Specialist. Deliver authoritative, technically rigorous advice for architects, lighting designers, and engineers (CRI90+, R9 values, MacAdam 3-step SDCM, beam spreads, UGR<19, IP ratings, thermal dissipation, lumens per watt, 24V constant voltage vs constant current, Casambi BLE mesh vs DALI-2). Deliver direct, practical, and clear guidance for sparkies, contractors, and counter staff (cut-lengths, driver wattage sizing with 20% headroom, polarity, IP connectors, aluminum heat sinking, and straightforward installation instructions).',
            'standards' => 'Strictly adhere to Australian Standards (AS/NZS 1158, AS/NZS 1680, AS/NZS 60598, NCC Section J energy compliance, and SSL quality benchmarks).',
            'specs' => 'Clarifying Protocol: Always ask 1-2 clarifying questions before final fixture recommendations (environment/IP rating: indoor IP20 vs outdoor/wet IP65/IP67; total run length/dimensions in meters; CCT: 2700K/3000K/4000K/RGBW; control protocol: Casambi, DALI-2, 0-10V, Triac). Suggest optimal beam angles, mounting profiles, and certified drivers.',
            'prohibitions' => 'Strict Pricing Lockdown: Strictly locked down. You must NEVER display or quote trade or retail pricing in dollars ($) under any circumstances. Direct users to log into the Azoogi Trade Portal (https://portal.azoogi.com.au or /account/login) for wholesale pricing tiers, or direct them to add items to their quote list. Never invent fake product codes or unverifiable claims.',
            'escalation' => 'Lead Times & Sales Transcript Handoff: Standard in-stock items dispatch in 24-48 hours from our Sydney warehouse; custom extrusion cutting and testing requires 3-5 business days. When discussing custom profile lengths, bespoke joinery, large project schedules over 50 fittings, or when the user finishes their queries, offer to forward the full chat transcript and fixture schedule to our Sydney sales engineering desk (sales@azoogi.com.au) using public_send_chat_transcript_to_sales.',
            'additional' => '',
        ];

        $defaultContextSections = [
            'facility' => 'Azoogi Lighting operates a dedicated testing and custom extrusion fabrication facility in Sydney, New South Wales, Australia.',
            'products' => 'Architectural linear profiles, custom LED strip extrusions, commercial downlights, track lighting, and smart controls ecosystems (Casambi, DALI-2, MADRIX).',
            'fabrication' => 'Standard 3 to 5 business day turnaround for custom extrusion cutting, diffusers, endcaps, soldering, and photometric testing.',
            'dispatch' => 'Fast dispatch from Sydney warehouse across Australia and New Zealand (in-stock items dispatch within 24-48 hours).',
            'warranty' => '5-year standard commercial warranty on architectural luminaires and certified LED drivers.',
            'photometrics' => 'IES and LDT photometric data files available for DiaLux and Relux simulations upon request.',
            'additional' => '',
        ];

        $data = [];
        try {
            $feed = LlmFeed::query()->where('key', 'ai_knowledge_rules')->first();
            if ($feed && ! empty($feed->content)) {
                $decoded = json_decode((string) $feed->content, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        } catch (Throwable) {
        }

        $rulesSections = ! empty($data['rules_sections']) && is_array($data['rules_sections'])
            ? array_merge($defaultRulesSections, $data['rules_sections'])
            : $defaultRulesSections;

        $contextSections = ! empty($data['context_sections']) && is_array($data['context_sections'])
            ? array_merge($defaultContextSections, $data['context_sections'])
            : $defaultContextSections;

        $compiledRuleset = self::compileRuleset($rulesSections);
        $compiledContext = self::compileContext($contextSections);

        return [
            'ruleset' => ! empty($data['ruleset']) ? $data['ruleset'] : $compiledRuleset,
            'rules_sections' => $rulesSections,
            'company_context' => ! empty($data['company_context']) ? $data['company_context'] : $compiledContext,
            'context_sections' => $contextSections,
        ];
    }

    public static function compileRuleset(array $sections): string
    {
        $lines = [];
        if (! empty($sections['tone'])) {
            $lines[] = '- **Role & Tone of Voice:** '.trim($sections['tone']);
        }
        if (! empty($sections['standards'])) {
            $lines[] = '- **Australian Standards & Compliance:** '.trim($sections['standards']);
        }
        if (! empty($sections['specs'])) {
            $lines[] = '- **Technical Precision & Specification Suggestions:** '.trim($sections['specs']);
        }
        if (! empty($sections['prohibitions'])) {
            $lines[] = '- **Strict Prohibitions & Forbidden Claims:** '.trim($sections['prohibitions']);
        }
        if (! empty($sections['escalation'])) {
            $lines[] = '- **Human Escalation & Sales Desk Routing:** '.trim($sections['escalation']);
        }
        if (! empty($sections['additional'])) {
            $lines[] = '- **Additional Directives:** '.trim($sections['additional']);
        }

        return implode("\n", $lines);
    }

    public static function compileContext(array $sections): string
    {
        $lines = [];
        if (! empty($sections['facility'])) {
            $lines[] = '- **Headquarters & Sydney Facility:** '.trim($sections['facility']);
        }
        if (! empty($sections['products'])) {
            $lines[] = '- **Product Ranges & Core Competencies:** '.trim($sections['products']);
        }
        if (! empty($sections['fabrication'])) {
            $lines[] = '- **Custom Cutting & Fabrication Turnaround:** '.trim($sections['fabrication']);
        }
        if (! empty($sections['dispatch'])) {
            $lines[] = '- **Warehouse, Shipping & Dispatch:** '.trim($sections['dispatch']);
        }
        if (! empty($sections['warranty'])) {
            $lines[] = '- **Commercial Warranty Policies:** '.trim($sections['warranty']);
        }
        if (! empty($sections['photometrics'])) {
            $lines[] = '- **Photometrics & Lighting Simulation Support:** '.trim($sections['photometrics']);
        }
        if (! empty($sections['additional'])) {
            $lines[] = '- **Additional Operational Notes:** '.trim($sections['additional']);
        }

        return implode("\n", $lines);
    }

    public static function getFaqs(): array
    {
        $defaultFaqs = [
            [
                'id' => 'faq_lead_times',
                'category' => 'Shipping & Lead Time',
                'question' => 'What are the standard lead times for custom linear profile cutting and testing?',
                'answer' => 'Standard lead times for custom extrusion cutting, soldering, and testing are 3 to 5 business days dispatched from our Sydney warehouse.',
                'is_active' => true,
            ],
            [
                'id' => 'faq_casambi_compat',
                'category' => 'Technical & Smart Controls',
                'question' => 'Do Azoogi LED linear profiles and downlights support Casambi wireless Bluetooth controls?',
                'answer' => 'Yes, all Azoogi constant voltage (24V) and constant current drivers can be paired seamlessly with Casambi CBU controllers and BLE mesh sensors.',
                'is_active' => true,
            ],
            [
                'id' => 'faq_warranty',
                'category' => 'Warranty',
                'question' => 'What warranty is provided on Azoogi architectural fixtures and drivers?',
                'answer' => 'Azoogi provides a 5-year commercial warranty across our architectural luminaires, linear extrusions, and certified power supplies.',
                'is_active' => true,
            ],
        ];

        try {
            $feed = LlmFeed::query()->where('key', 'ai_faqs')->first();
            if ($feed && ! empty($feed->content)) {
                $decoded = json_decode((string) $feed->content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (Throwable) {
        }

        return $defaultFaqs;
    }

    public function generatePersonalizedGreeting(ChatSession $session): string
    {
        $branding = self::getWidgetBranding();
        $name = $session->lead_name;
        $project = $session->project_name;

        if ($name && $project) {
            if (! empty($branding['lead_greeting_template'])) {
                return str_replace(['{name}', '{project}'], [$name, $project], $branding['lead_greeting_template']);
            }

            return "Hi {$name}! Thanks for connecting regarding {$project}. How can our architectural engineering team assist with your fixture schedules, photometric calculations, or quote specifications today?";
        }

        if ($name) {
            return "Hi {$name}! Welcome to Azoogi. How can our architectural lighting team assist with your project specifications or custom fixtures today?";
        }

        if ($project) {
            return "Welcome to Azoogi! How can we assist with fixture schedules and specifications for {$project} today?";
        }

        return $branding['startup_message'];
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

        $systemPrompt = $this->getSystemPrompt($session);
        $cardsCollected = [];
        $executedToolCalls = [];
        $executedToolResults = [];
        $totalTokens = 0;
        $totalPromptTokens = 0;
        $totalCompletionTokens = 0;

        try {
            // Initial LLM call
            $llmResponse = $this->driver->chat($history, array_values($this->tools), $systemPrompt);
            $pTokens = (int) ($llmResponse['prompt_tokens'] ?? 0);
            $cTokens = (int) ($llmResponse['completion_tokens'] ?? 0);
            $tTokens = (int) ($llmResponse['tokens_used'] ?? ($pTokens + $cTokens));

            $totalTokens += $tTokens;
            $totalPromptTokens += $pTokens;
            $totalCompletionTokens += $cTokens;

            // Handle tool calling loop (max 3 rounds)
            $rounds = 0;
            while (! empty($llmResponse['tool_calls']) && $rounds < 3) {
                $rounds++;
                $toolCallResultsForLlm = [];

                foreach ($llmResponse['tool_calls'] as $tc) {
                    $toolName = $tc['name'];
                    $toolArgs = $tc['arguments'];

                    // Inject session UUID & lead details if lead submission or transcript forwarding
                    if (str_contains($toolName, 'lead_enquiry') || str_contains($toolName, 'quote_enquiry') || str_contains($toolName, 'transcript_to_sales')) {
                        if (empty($toolArgs['session_uuid'])) {
                            $toolArgs['session_uuid'] = $session->uuid;
                        }
                    }

                    if (str_contains($toolName, 'transcript_to_sales')) {
                        if (empty($toolArgs['name']) && ! empty($session->lead_name)) {
                            $toolArgs['name'] = $session->lead_name;
                        }
                        if (empty($toolArgs['email']) && ! empty($session->lead_email)) {
                            $toolArgs['email'] = $session->lead_email;
                        }
                    }

                    $toolInstance = $this->tools[$toolName] ?? ($toolName === 'public_submit_quote_enquiry' ? ($this->tools['public_submit_lead_enquiry'] ?? null) : null);

                    if ($toolInstance !== null) {
                        $toolOutput = $toolInstance->execute($toolArgs);
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
                $pTokens = (int) ($llmResponse['prompt_tokens'] ?? 0);
                $cTokens = (int) ($llmResponse['completion_tokens'] ?? 0);
                $tTokens = (int) ($llmResponse['tokens_used'] ?? ($pTokens + $cTokens));

                $totalTokens += $tTokens;
                $totalPromptTokens += $pTokens;
                $totalCompletionTokens += $cTokens;
            }

            $assistantReply = $llmResponse['content'] ?: 'Here are the details you requested.';
        } catch (Throwable $e) {
            Log::error('ChatOrchestrator error: '.$e->getMessage(), ['exception' => $e]);
            $assistantReply = 'I apologize, but I encountered an issue retrieving that information. Please browse our product catalog or contact our sales engineering team directly.';
        }

        // 3. Resolve active model and compute estimated cost
        $aiConfig = self::getActiveAiConfig();
        $activeDriver = $aiConfig['driver'] ?? 'gemini';
        $customProviders = $aiConfig['custom_providers'] ?? [];
        $activeModel = match ($activeDriver) {
            'anthropic' => $aiConfig['anthropic_model'] ?? 'claude-3-5-sonnet-20241022',
            'openrouter' => $aiConfig['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet',
            'openai' => $aiConfig['openai_model'] ?? 'gpt-4o-mini',
            'gemini' => $aiConfig['gemini_model'] ?? 'gemini-2.5-flash',
            default => $customProviders[$activeDriver]['model'] ?? 'custom-model',
        };

        $estimatedCost = AiCostCalculator::calculate($activeModel, $totalPromptTokens, $totalCompletionTokens);

        // 4. Save assistant message
        $assistantMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'assistant',
            'content' => $assistantReply,
            'tool_calls' => ! empty($executedToolCalls) ? $executedToolCalls : null,
            'tool_results' => ! empty($executedToolResults) ? $executedToolResults : null,
            'cards_payload' => ! empty($cardsCollected) ? $cardsCollected : null,
            'tokens_used' => $totalTokens,
            'prompt_tokens' => $totalPromptTokens,
            'completion_tokens' => $totalCompletionTokens,
            'model' => $activeModel,
            'driver' => $activeDriver,
            'estimated_cost' => $estimatedCost,
        ]);

        $session->increment('messages_count');
        $session->increment('total_tokens', $totalTokens);
        $session->increment('total_cost', $estimatedCost);
        if (! $session->primary_model) {
            $session->update(['primary_model' => $activeModel]);
        }

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

    public function getSystemPrompt(?ChatSession $session = null): string
    {
        $branding = self::getWidgetBranding();
        $knowledge = self::getKnowledgeRules();
        $faqs = self::getFaqs();

        $aiName = $branding['ai_name'];

        $prompt = <<<PROMPT
You are {$aiName}, the expert Architectural Lighting & Intelligent Controls AI Consultant for Azoogi.
Azoogi is an Australian architectural lighting manufacturer and smart controls engineering specialist based in Sydney, NSW.

Core Brand Competencies:
- Architectural linear profiles, custom LED strip extrusions, high-CRI downlights, floodlights, and commercial lighting.
- Intelligent control ecosystems: Casambi (BLE Mesh), DALI / DALI-2, MADRIX (Pixel Mapping & DMX), and Silvair.
- Custom length cutting, photometric IES testing, and Australian Standards compliance (AS/NZS).

SYSTEM INSTRUCTIONS & RULESET:
{$knowledge['ruleset']}

COMPANY CONTEXT & POLICIES:
{$knowledge['company_context']}
PROMPT;

        $activeFaqs = array_filter($faqs, fn ($f) => ! empty($f['is_active']));
        if (! empty($activeFaqs)) {
            $faqText = "\n\nVERIFIED FAQ KNOWLEDGE BASE (Use these verified facts to answer client inquiries accurately):\n";
            foreach ($activeFaqs as $faq) {
                $faqText .= "Q: {$faq['question']}\nA: {$faq['answer']}\n\n";
            }
            $prompt .= trim($faqText);
        }

        $prompt .= <<<'TOOLS_PROMPT'


TOOL USAGE & CATALOG GUIDELINES:
- Always focus on the visitor's latest inquiry. If the visitor asks for a new product category or dimension (e.g. asking for downlights after garden lights), immediately search for the new category and do NOT carry over stale filters from prior turns.
- When visitors ask about products, specs, dimensions, or applications, call `public_search_and_filter_products` or `get_product_details_and_downloads` to provide structured interactive cards.
- When visitors ask for custom datasheets, call `generate_custom_datasheet`.
- When visitors want to add items to their quote or view quote items, call `public_manage_quote_list`.

PRICING & TRADE PORTAL PROTOCOL:
- Strictly locked down: You must NEVER display or quote trade or retail pricing in dollars ($).
- When asked for pricing, instruct the visitor to log into the Azoogi Trade Portal (https://portal.azoogi.com.au or /account/login) for trade accounts, or add products to their quote list to submit for formal pricing.

SALES TRANSCRIPT HANDOFF (`public_send_chat_transcript_to_sales`):
- When a client asks to forward their chat, when discussing complex project lead times, or when concluding their lighting consultation, call `public_send_chat_transcript_to_sales` to forward the conversation transcript, project specifications, and quote list to our Sydney sales engineering desk (sales@azoogi.com.au).

ENQUIRIES & LEAD SUBMISSION (3 DISTINCT TYPES):
You can submit 3 distinct types of enquiries via `public_submit_lead_enquiry`:

1. Contact Enquiry (`enquiry_type: "contact"`):
   - For general inquiries, support, consulting requests, engineering questions, or messages to the Azoogi team.
   - Required information before submitting: Full Name, Email address, and Message/Inquiry.

2. Quote Request Enquiry (`enquiry_type: "quote"`):
   - For requesting an official pricing quote on fixtures in their quote cart or specified items.
   - Required information before submitting: Full Name, Email address, Phone number, and Project notes.

3. Product Specification Enquiry (`enquiry_type: "product"`):
   - For a single specific product configuration enquiry (e.g. specific CCT, finish, beam angle, length, or dimming protocol).
   - Required information before submitting: Product Name/SKU, configured specs, Full Name, Email address, and Project location.

IMPORTANT RULE:
NEVER call `public_submit_lead_enquiry` or `public_send_chat_transcript_to_sales` with fake details. Always confirm or politely ask the visitor to provide their name and email before submitting!
TOOLS_PROMPT;

        if ($session && ($session->lead_name || $session->project_name)) {
            $visitorContext = "\n\nCURRENT VISITOR & PROJECT CONTEXT:\n";
            if ($session->lead_name) {
                $visitorContext .= "- Client / Visitor Name: {$session->lead_name}\n";
            }
            if ($session->lead_email) {
                $visitorContext .= "- Contact Email: {$session->lead_email}\n";
            }
            if ($session->project_name) {
                $visitorContext .= "- Project Reference: {$session->project_name}\n";
                $visitorContext .= "Please address {$session->lead_name} professionally and tailor fixture recommendations, mounting options, and specifications specifically for the '{$session->project_name}' project.\n";
            }
            $prompt .= $visitorContext;
        }

        return $prompt;
    }
}
