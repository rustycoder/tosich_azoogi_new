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
    public function index(): View
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

        return view('dashboard.ai.config', [
            'aiConfig' => $aiConfig,
            'apiKeysStatus' => $apiKeysStatus,
            'customProviders' => $aiConfig['custom_providers'] ?? [],
            'sessionsCount' => $sessionsCount,
            'messagesCount' => $messagesCount,
            'activeSessionsCount' => $activeSessionsCount,
        ]);
    }

    public function update(Request $request): RedirectResponse
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
            ->route('dashboard.ai.config')
            ->with('status', "AI Configuration saved: Active model provider set to [{$validated['driver']}].");
    }

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
            ->route('dashboard.ai.config')
            ->with('status', "Custom AI Provider [{$validated['name']}] created successfully.");
    }

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
            ->route('dashboard.ai.config')
            ->with('status', "Custom AI Provider [{$providerName}] removed.");
    }

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
