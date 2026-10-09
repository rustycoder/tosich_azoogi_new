<?php

declare(strict_types=1);

namespace App\Services\Chat\Drivers;

use App\Services\Chat\Contracts\IChatLlmDriver;
use App\Services\Chat\Contracts\IChatTool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiDriver implements IChatLlmDriver
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $model = null,
        protected ?string $baseUrl = null,
        protected array $extraBody = [],
    ) {
        $this->apiKey = $apiKey ?: (string) config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = $model ?: (string) config('services.openai.chat_model', env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'));
        $this->baseUrl = rtrim($baseUrl ?: (string) config('services.openai.base_url', env('OPENAI_BASE_URL', 'https://api.openai.com/v1')), '/');
    }

    public function chat(array $messages, array $tools = [], string $systemPrompt = ''): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY is not configured in .env');
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

        $payload = [
            'model' => $this->model,
            'messages' => $formattedMessages,
            'temperature' => 0.4,
        ];

        if (! empty($this->extraBody)) {
            $payload = array_merge($payload, $this->extraBody);
        } elseif (str_contains((string) $this->baseUrl, 'prisha') || str_contains((string) $this->baseUrl, 'ai.')) {
            $payload['chat_id'] = 'api_bypass_fix';
        }

        if (! empty($tools)) {
            $uniqueTools = [];
            foreach ($tools as $t) {
                if ($t instanceof IChatTool) {
                    $uniqueTools[$t->getName()] = $t;
                }
            }
            $payload['tools'] = array_map(function (IChatTool $t) {
                return [
                    'type' => 'function',
                    'function' => [
                        'name' => $t->getName(),
                        'description' => $t->getDescription(),
                        'parameters' => $t->getParameters(),
                    ],
                ];
            }, array_values($uniqueTools));
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if (! $response->successful()) {
            Log::error('OpenAI Chat Error', ['body' => $response->body()]);
            throw new RuntimeException('OpenAI API error: '.$response->body());
        }

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
                ];
            }
        }

        $promptTokens = (int) ($json['usage']['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($json['usage']['completion_tokens'] ?? 0);
        $tokensUsed = (int) ($json['usage']['total_tokens'] ?? ($promptTokens + $completionTokens));

        return [
            'content' => $choice['content'] ?? null,
            'tool_calls' => $parsedToolCalls,
            'tokens_used' => $tokensUsed,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
        ];
    }
}
