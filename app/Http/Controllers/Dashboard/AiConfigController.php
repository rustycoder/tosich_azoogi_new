<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\LlmFeed;
use App\Services\Chat\ChatOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AiConfigController extends Controller
{
    /**
     * Display AI Models, API Keys & Rate Schedule Configuration.
     */
    public function models(): View
    {
        $aiConfig = ChatOrchestrator::getActiveAiConfig();

        $apiKeysStatus = [
            'gemini' => ! empty($aiConfig['gemini_api_key']),
            'anthropic' => ! empty($aiConfig['anthropic_api_key']),
            'openrouter' => ! empty($aiConfig['openrouter_api_key']),
            'openai' => ! empty($aiConfig['openai_api_key']),
        ];

        $sessionsCount = ChatSession::count();
        $messagesCount = ChatMessage::count();
        $activeSessionsCount = ChatSession::where('status', 'active')->count();

        $totalTokens = (int) ChatSession::sum('total_tokens') ?: (int) ChatMessage::sum('tokens_used');
        $totalCost = (float) ChatSession::sum('total_cost') ?: (float) ChatMessage::sum('estimated_cost');

        return view('dashboard.ai.models', [
            'aiConfig' => $aiConfig,
            'apiKeysStatus' => $apiKeysStatus,
            'customProviders' => $aiConfig['custom_providers'] ?? [],
            'sessionsCount' => $sessionsCount,
            'messagesCount' => $messagesCount,
            'activeSessionsCount' => $activeSessionsCount,
            'totalTokens' => $totalTokens,
            'totalCost' => $totalCost,
        ]);
    }

    /**
     * Backward compatibility index alias.
     */
    public function index(): View
    {
        return $this->models();
    }

    /**
     * Update AI Models & Provider configuration.
     */
    public function updateModels(Request $request): RedirectResponse
    {
        $existingFeed = LlmFeed::query()->where('key', 'ai_chat_config')->first();
        $existingConfig = [];
        if ($existingFeed && ! empty($existingFeed->content)) {
            $decoded = json_decode((string) $existingFeed->content, true);
            if (is_array($decoded)) {
                $existingConfig = $decoded;
            }
        }

        $customProviders = $existingConfig['custom_providers'] ?? [];
        $allowedDrivers = array_merge(['gemini', 'anthropic', 'openrouter', 'openai'], array_keys($customProviders));

        $validated = $request->validate([
            'driver' => ['required', 'string', 'in:'.implode(',', $allowedDrivers)],
            'gemini_api_key' => ['nullable', 'string', 'max:500'],
            'gemini_model' => ['nullable', 'string', 'max:100'],
            'anthropic_api_key' => ['nullable', 'string', 'max:500'],
            'anthropic_model' => ['nullable', 'string', 'max:100'],
            'openrouter_api_key' => ['nullable', 'string', 'max:500'],
            'openrouter_model' => ['nullable', 'string', 'max:150'],
            'openai_api_key' => ['nullable', 'string', 'max:500'],
            'openai_model' => ['nullable', 'string', 'max:100'],
        ]);

        $resolveKey = function (string $keyName) use ($request, $existingConfig): ?string {
            if ($request->has($keyName)) {
                $val = trim((string) $request->input($keyName, ''));
                if ($val !== '') {
                    return $val;
                }

                return $existingConfig[$keyName] ?? null;
            }

            return $existingConfig[$keyName] ?? null;
        };

        $configPayload = [
            'driver' => $validated['driver'],
            'gemini_api_key' => $resolveKey('gemini_api_key'),
            'gemini_model' => trim($validated['gemini_model'] ?? '') ?: 'gemini-2.5-flash',
            'anthropic_api_key' => $resolveKey('anthropic_api_key'),
            'anthropic_model' => trim($validated['anthropic_model'] ?? '') ?: 'claude-3-5-sonnet-20241022',
            'openrouter_api_key' => $resolveKey('openrouter_api_key'),
            'openrouter_model' => trim($validated['openrouter_model'] ?? '') ?: 'anthropic/claude-3.5-sonnet',
            'openai_api_key' => $resolveKey('openai_api_key'),
            'openai_model' => trim($validated['openai_model'] ?? '') ?: 'gpt-4o-mini',
            'custom_providers' => $customProviders,
        ];

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_chat_config'],
            [
                'content' => json_encode($configPayload, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.models')
            ->with('status', "AI Model Configuration saved: Active model provider set to [{$validated['driver']}].");
    }

    /**
     * Backward compatibility update alias.
     */
    public function update(Request $request): RedirectResponse
    {
        return $this->updateModels($request);
    }

    /**
     * Display Widget & Branding configuration view.
     */
    public function widget(): View
    {
        $branding = ChatOrchestrator::getWidgetBranding();

        return view('dashboard.ai.widget', [
            'branding' => $branding,
        ]);
    }

    /**
     * Update Widget & Branding settings.
     */
    public function updateWidget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_name' => ['required', 'string', 'max:100'],
            'ai_avatar' => ['required', 'string', 'in:spark,lightbulb,leaf,building,robot,custom'],
            'ai_custom_avatar_url' => ['nullable', 'string', 'max:500'],
            'ai_subtitle' => ['required', 'string', 'max:150'],
            'startup_message' => ['required', 'string', 'max:1000'],
            'lead_greeting_template' => ['nullable', 'string', 'max:1000'],
            'intake_enabled' => ['nullable', 'boolean'],
            'intake_require_project' => ['nullable', 'boolean'],
            'intake_require_email' => ['nullable', 'boolean'],
            'intake_require_name' => ['nullable', 'boolean'],
            'starter_chips' => ['nullable', 'array'],
            'starter_chips.*.icon' => ['nullable', 'string', 'max:20'],
            'starter_chips.*.label' => ['required', 'string', 'max:80'],
            'starter_chips.*.prompt' => ['required', 'string', 'max:250'],
        ]);

        $starterChips = [];
        if (! empty($validated['starter_chips']) && is_array($validated['starter_chips'])) {
            foreach ($validated['starter_chips'] as $chip) {
                if (! empty($chip['label']) && ! empty($chip['prompt'])) {
                    $starterChips[] = [
                        'icon' => trim((string) ($chip['icon'] ?? '💡')),
                        'label' => trim((string) $chip['label']),
                        'prompt' => trim((string) $chip['prompt']),
                    ];
                }
            }
        }

        $brandingPayload = [
            'ai_name' => trim($validated['ai_name']),
            'ai_avatar' => $validated['ai_avatar'],
            'ai_custom_avatar_url' => ! empty($validated['ai_custom_avatar_url']) ? trim($validated['ai_custom_avatar_url']) : null,
            'ai_subtitle' => trim($validated['ai_subtitle']),
            'startup_message' => trim($validated['startup_message']),
            'lead_greeting_template' => ! empty($validated['lead_greeting_template']) ? trim($validated['lead_greeting_template']) : null,
            'starter_chips' => $starterChips,
            'intake_enabled' => $request->boolean('intake_enabled', true),
            'intake_require_project' => $request->boolean('intake_require_project', false),
            'intake_require_email' => $request->boolean('intake_require_email', false),
            'intake_require_name' => $request->boolean('intake_require_name', false),
        ];

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_widget_branding'],
            [
                'content' => json_encode($brandingPayload, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.widget')
            ->with('status', 'AI Chat Widget & Branding settings saved successfully.');
    }

    /**
     * Display Knowledge, Rules & FAQ configuration view.
     */
    public function knowledge(): View
    {
        $knowledge = ChatOrchestrator::getKnowledgeRules();
        $faqs = ChatOrchestrator::getFaqs();
        $compiledPrompt = app(ChatOrchestrator::class)->getSystemPrompt();

        return view('dashboard.ai.knowledge', [
            'knowledge' => $knowledge,
            'faqs' => $faqs,
            'compiledPrompt' => $compiledPrompt,
        ]);
    }

    /**
     * Update Ruleset & Company Context.
     */
    public function updateKnowledge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ruleset' => ['required', 'string', 'max:5000'],
            'company_context' => ['required', 'string', 'max:5000'],
        ]);

        $payload = [
            'ruleset' => trim($validated['ruleset']),
            'company_context' => trim($validated['company_context']),
        ];

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_knowledge_rules'],
            [
                'content' => json_encode($payload, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.knowledge')
            ->with('status', 'AI Ruleset & Company Context updated successfully.');
    }

    /**
     * Store or update an FAQ item.
     */
    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:300'],
            'answer' => ['required', 'string', 'max:1500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faqs = ChatOrchestrator::getFaqs();
        $faqId = ! empty($validated['id']) ? $validated['id'] : 'faq_'.Str::slug(substr($validated['question'], 0, 30), '_').'_'.substr(md5(uniqid()), 0, 4);

        $updated = false;
        foreach ($faqs as &$item) {
            if ($item['id'] === $faqId) {
                $item['category'] = trim($validated['category']);
                $item['question'] = trim($validated['question']);
                $item['answer'] = trim($validated['answer']);
                $item['is_active'] = $request->boolean('is_active', true);
                $updated = true;
                break;
            }
        }
        unset($item);

        if (! $updated) {
            $faqs[] = [
                'id' => $faqId,
                'category' => trim($validated['category']),
                'question' => trim($validated['question']),
                'answer' => trim($validated['answer']),
                'is_active' => $request->boolean('is_active', true),
            ];
        }

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_faqs'],
            [
                'content' => json_encode($faqs, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.knowledge')
            ->with('status', 'FAQ item saved successfully.');
    }

    /**
     * Delete an FAQ item.
     */
    public function deleteFaq(string $id): RedirectResponse
    {
        $faqs = ChatOrchestrator::getFaqs();
        $faqs = array_values(array_filter($faqs, fn ($f) => ($f['id'] ?? '') !== $id));

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_faqs'],
            [
                'content' => json_encode($faqs, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.knowledge')
            ->with('status', 'FAQ item removed.');
    }

    /**
     * Store Custom AI Provider.
     */
    public function storeCustomProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:openai,anthropic,gemini'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'api_key' => ['required', 'string', 'max:500'],
            'model' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'set_active' => ['nullable', 'boolean'],
        ]);

        $existingFeed = LlmFeed::query()->where('key', 'ai_chat_config')->first();
        $config = [];
        if ($existingFeed && ! empty($existingFeed->content)) {
            $decoded = json_decode((string) $existingFeed->content, true);
            if (is_array($decoded)) {
                $config = $decoded;
            }
        }

        $providerId = 'custom_'.Str::slug($validated['name'], '_').'_'.substr(md5(uniqid()), 0, 4);

        $customProviders = $config['custom_providers'] ?? [];
        $customProviders[$providerId] = [
            'id' => $providerId,
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'base_url' => ! empty($validated['base_url']) ? rtrim(trim($validated['base_url']), '/') : null,
            'api_key' => trim($validated['api_key']),
            'model' => trim($validated['model']),
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            'created_at' => now()->toIso8601String(),
        ];

        $config['custom_providers'] = $customProviders;

        if ($request->boolean('set_active')) {
            $config['driver'] = $providerId;
        }

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_chat_config'],
            [
                'content' => json_encode($config, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.models')
            ->with('status', "Custom AI Provider [{$validated['name']}] created successfully.");
    }

    /**
     * Delete Custom AI Provider.
     */
    public function deleteCustomProvider(string $id): RedirectResponse
    {
        $existingFeed = LlmFeed::query()->where('key', 'ai_chat_config')->first();
        $config = [];
        if ($existingFeed && ! empty($existingFeed->content)) {
            $decoded = json_decode((string) $existingFeed->content, true);
            if (is_array($decoded)) {
                $config = $decoded;
            }
        }

        $customProviders = $config['custom_providers'] ?? [];
        $providerName = $customProviders[$id]['name'] ?? $id;

        unset($customProviders[$id]);
        $config['custom_providers'] = $customProviders;

        if (($config['driver'] ?? '') === $id) {
            $config['driver'] = 'anthropic';
        }

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_chat_config'],
            [
                'content' => json_encode($config, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.ai.models')
            ->with('status', "Custom AI Provider [{$providerName}] removed.");
    }

    /**
     * Test Connection to an AI Provider.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:150'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:openai,anthropic,gemini'],
        ]);

        $driverName = $validated['driver'];
        $model = ! empty($validated['model']) ? trim($validated['model']) : null;
        $apiKey = ! empty($validated['api_key']) ? trim($validated['api_key']) : null;
        $baseUrl = ! empty($validated['base_url']) ? rtrim(trim($validated['base_url']), '/') : null;
        $type = ! empty($validated['type']) ? trim($validated['type']) : null;

        $startTime = microtime(true);

        try {
            $driver = ChatOrchestrator::makeDriver(
                driverName: $driverName,
                model: $model,
                apiKey: $apiKey,
                baseUrl: $baseUrl,
                type: $type
            );

            $testPrompt = 'Respond with: "Connection OK to Azoogi backend." followed by one short sentence confirming your active model name.';

            $result = $driver->chat(
                messages: [
                    ['role' => 'user', 'content' => $testPrompt],
                ],
                tools: [],
                systemPrompt: 'You are a test ping agent. Answer concisely in under 20 words.'
            );

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => true,
                'driver' => $driverName,
                'model' => $model,
                'response' => $result['content'] ?? 'No text response',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'latency_ms' => $latencyMs,
            ]);
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => false,
                'driver' => $driverName,
                'model' => $model,
                'error' => $e->getMessage(),
                'latency_ms' => $latencyMs,
            ], 422);
        }
    }
}
