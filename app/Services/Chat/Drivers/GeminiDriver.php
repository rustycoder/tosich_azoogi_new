<?php

declare(strict_types=1);

namespace App\Services\Chat\Drivers;

use App\Services\Chat\Contracts\IChatLlmDriver;
use App\Services\Chat\Contracts\IChatTool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiDriver implements IChatLlmDriver
{
    public function __construct(
        protected ?string $apiKey = null,
        protected string $model = 'gemini-3.5-flash',
        protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai',
    ) {
        $this->apiKey = $apiKey ?: (string) config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->model = (string) config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3.5-flash'));
        $this->baseUrl = rtrim((string) config('services.gemini.base_url', env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai')), '/');
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<int, IChatTool>  $tools
     * @return array{content: string|null, tool_calls: array<int, array{id: string, name: string, arguments: array<string, mixed>, raw?: array<string, mixed>}>, raw_message?: array<string, mixed>, tokens_used?: int}
     */
    public function chat(array $messages, array $tools = [], string $systemPrompt = ''): array
    {
        if (empty($this->apiKey)) {
            return app(MockLlmDriver::class)->chat($messages, $tools, $systemPrompt);
        }

        $formattedMessages = [];
        if (! empty($systemPrompt)) {
            $formattedMessages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        foreach ($messages as $msg) {
            $formattedMessages[] = $msg;
        }

        $toolDefinitions = [];
        if (! empty($tools)) {
            $toolDefinitions = array_map(function (IChatTool $t) {
                return [
                    'type' => 'function',
                    'function' => [
                        'name' => $t->getName(),
                        'description' => $t->getDescription(),
                        'parameters' => $t->getParameters(),
                    ],
                ];
            }, $tools);
        }

        // List candidate models for automatic failover under rate limits (429) or high demand (503)
        $modelsToTry = array_unique([
            $this->model,
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
            'gemini-flash-lite-latest',
            'gemini-3.8-flash',
        ]);

        foreach ($modelsToTry as $candidateModel) {
            try {
                $payload = [
                    'model' => $candidateModel,
                    'messages' => $formattedMessages,
                    'temperature' => 0.3,
                ];

                if (! empty($toolDefinitions)) {
                    $payload['tools'] = $toolDefinitions;
                }

                $response = Http::withToken($this->apiKey)
                    ->timeout(20)
                    ->post("{$this->baseUrl}/chat/completions", $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    $choice = $json['choices'][0]['message'] ?? [];
                    $rawToolCalls = $choice['tool_calls'] ?? [];

                    $parsedToolCalls = [];
                    foreach ($rawToolCalls as $tc) {
                        if (($tc['type'] ?? '') === 'function') {
                            $args = json_decode((string) ($tc['function']['arguments'] ?? '{}'), true);
                            $parsedToolCalls[] = [
                                'id' => (string) ($tc['id'] ?? uniqid('call_')),
                                'name' => (string) ($tc['function']['name'] ?? ''),
                                'arguments' => is_array($args) ? $args : [],
                                'raw' => $tc,
                            ];
                        }
                    }

                    return [
                        'content' => $choice['content'] ?? null,
                        'tool_calls' => $parsedToolCalls,
                        'raw_message' => $choice,
                        'tokens_used' => (int) ($json['usage']['total_tokens'] ?? 0),
                    ];
                }

                // If 429 (rate limit) or 503 (model overloaded), try next candidate model
                if (in_array($response->status(), [429, 503], true)) {
                    Log::warning("Gemini model {$candidateModel} returned {$response->status()}, rotating to next model.");

                    continue;
                }

                Log::error('Gemini API error', [
                    'model' => $candidateModel,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (Throwable $e) {
                Log::warning("Gemini request exception for model {$candidateModel}: ".$e->getMessage());
            }
        }

        // Fallback to Mock driver if all Gemini attempts fail
        Log::warning('All Gemini models exhausted/failed. Falling back to local MockLlmDriver.');

        return app(MockLlmDriver::class)->chat($messages, $tools, $systemPrompt);
    }
}
