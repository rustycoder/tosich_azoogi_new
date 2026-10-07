<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\LlmFeed;
use App\Models\Product;
use App\Models\Project;
use App\Services\Chat\ChatOrchestrator;
use App\Support\LlmsTxtBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class LlmFeedController extends Controller
{
    public function __construct(
        private LlmsTxtBuilder $builder,
    ) {}

    public function index(): View
    {
        $summaryFeed = LlmFeed::query()->where('key', LlmsTxtBuilder::FEED_KEY_SUMMARY)->first();
        $defaultSummary = $this->builder->buildDefaultSummaryMarkdown();
        $currentSummary = ($summaryFeed && ! empty($summaryFeed->content))
            ? (string) $summaryFeed->content
            : $defaultSummary;
        $isCustom = (bool) ($summaryFeed?->is_custom ?? false);

        $liveSummary = $this->builder->buildSummaryMarkdown();
        $liveFull = $this->builder->buildFullMarkdown();

        $productsCount = Product::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'publish')
                    ->orWhere('status', 'Publish');
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('slug')->where('slug', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('airtable_id')->where('airtable_id', '!=', '');
                });
            })
            ->count();

        $projectsCount = Project::query()
            ->where('status', Status::Active)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->count();

        $aiConfig = ChatOrchestrator::getActiveAiConfig();

        $apiKeysStatus = [
            'gemini' => ! empty($aiConfig['gemini_api_key']),
            'anthropic' => ! empty($aiConfig['anthropic_api_key']),
            'openrouter' => ! empty($aiConfig['openrouter_api_key']),
            'openai' => ! empty($aiConfig['openai_api_key']),
        ];

        return view('dashboard.llms.index', [
            'summaryFeed' => $summaryFeed,
            'summaryContent' => $currentSummary,
            'defaultSummary' => $defaultSummary,
            'isCustom' => $isCustom,
            'liveSummary' => $liveSummary,
            'liveFull' => $liveFull,
            'productsCount' => $productsCount,
            'projectsCount' => $projectsCount,
            'lastUpdated' => $summaryFeed?->updated_at,
            'aiConfig' => $aiConfig,
            'apiKeysStatus' => $apiKeysStatus,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'is_custom' => ['nullable', 'boolean'],
        ]);

        $content = trim((string) ($validated['content'] ?? ''));
        $isCustom = $request->boolean('is_custom');

        LlmFeed::query()->updateOrCreate(
            ['key' => LlmsTxtBuilder::FEED_KEY_SUMMARY],
            [
                'content' => $content !== '' ? $content : null,
                'is_custom' => $isCustom,
            ]
        );

        LlmsTxtBuilder::clearCache();
        $this->builder->getCachedSummary();
        $this->builder->getCachedFull();

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', 'LLM feed configuration saved and cache refreshed successfully.');
    }

    public function reset(): RedirectResponse
    {
        $defaultSummary = $this->builder->buildDefaultSummaryMarkdown();

        LlmFeed::query()->updateOrCreate(
            ['key' => LlmsTxtBuilder::FEED_KEY_SUMMARY],
            [
                'content' => $defaultSummary,
                'is_custom' => false,
            ]
        );

        LlmsTxtBuilder::clearCache();
        $this->builder->getCachedSummary();
        $this->builder->getCachedFull();

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', 'LLM summary feed has been reset to the default curated template.');
    }

    public function generate(): RedirectResponse
    {
        LlmsTxtBuilder::clearCache();
        $this->builder->getCachedSummary();
        $this->builder->getCachedFull();

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', 'All LLM and GEO feeds (/llms.txt and /llms-full.txt) regenerated successfully.');
    }

    public function updateAiConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'string', 'in:gemini,anthropic,openrouter,openai'],
            'gemini_api_key' => ['nullable', 'string', 'max:500'],
            'gemini_model' => ['nullable', 'string', 'max:100'],
            'anthropic_api_key' => ['nullable', 'string', 'max:500'],
            'anthropic_model' => ['nullable', 'string', 'max:100'],
            'openrouter_api_key' => ['nullable', 'string', 'max:500'],
            'openrouter_model' => ['nullable', 'string', 'max:150'],
            'openai_api_key' => ['nullable', 'string', 'max:500'],
            'openai_model' => ['nullable', 'string', 'max:100'],
        ]);

        $existingFeed = LlmFeed::query()->where('key', 'ai_chat_config')->first();
        $existingConfig = [];
        if ($existingFeed && ! empty($existingFeed->content)) {
            $decoded = json_decode((string) $existingFeed->content, true);
            if (is_array($decoded)) {
                $existingConfig = $decoded;
            }
        }

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
        ];

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_chat_config'],
            [
                'content' => json_encode($configPayload, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', "AI Settings updated: Active provider set to [{$validated['driver']}].");
    }

    public function testAiConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'string', 'in:gemini,anthropic,openrouter,openai'],
            'model' => ['nullable', 'string', 'max:150'],
        ]);

        $driverName = $validated['driver'];
        $model = ! empty($validated['model']) ? trim($validated['model']) : null;
        $startTime = microtime(true);

        try {
            $driver = ChatOrchestrator::makeDriver($driverName, $model);
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
