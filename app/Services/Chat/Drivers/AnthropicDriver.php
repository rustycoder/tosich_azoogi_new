<?php

declare(strict_types=1);

namespace App\Services\Chat\Drivers;

use App\Services\Chat\Contracts\IChatLlmDriver;
use App\Services\Chat\Contracts\IChatTool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AnthropicDriver implements IChatLlmDriver
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $model = null,
    ) {
        $this->apiKey = $apiKey ?: (string) config('services.anthropic.api_key', env('ANTHROPIC_API_KEY'));
        $this->model = $model ?: (string) config('services.anthropic.chat_model', env('ANTHROPIC_CHAT_MODEL', 'claude-3-5-sonnet-20241022'));
    }

    public function chat(array $messages, array $tools = [], string $systemPrompt = ''): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured in .env');
        }

        $formattedMessages = [];
        foreach ($messages as $msg) {
            $role = $msg['role'] === 'assistant' ? 'assistant' : 'user';
            $content = $msg['content'] ?? '';

            if ($msg['role'] === 'tool') {
                $formattedMessages[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $msg['tool_call_id'] ?? 'tool_1',
                            'content' => is_string($content) ? $content : json_encode($content),
                        ],
                    ],
                ];
            } elseif (! empty($msg['tool_calls'])) {
                $contentBlocks = [];
                if (! empty($content)) {
                    $contentBlocks[] = ['type' => 'text', 'text' => $content];
                }
                foreach ($msg['tool_calls'] as $tc) {
                    $contentBlocks[] = [
                        'type' => 'tool_use',
                        'id' => $tc['id'],
                        'name' => $tc['name'],
                        'input' => $tc['arguments'] ?? [],
                    ];
                }
                $formattedMessages[] = [
                    'role' => 'assistant',
                    'content' => $contentBlocks,
                ];
            } else {
                $formattedMessages[] = [
                    'role' => $role,
                    'content' => $content,
                ];
            }
        }

        $payload = [
            'model' => $this->model,
            'max_tokens' => 1024,
            'messages' => $formattedMessages,
        ];

        if (! empty($systemPrompt)) {
            $payload['system'] = $systemPrompt;
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
                    'name' => $t->getName(),
                    'description' => $t->getDescription(),
                    'input_schema' => $t->getParameters(),
                ];
            }, array_values($uniqueTools));
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', $payload);

        if (! $response->successful()) {
            Log::error('Anthropic Chat Error', ['body' => $response->body()]);
            throw new RuntimeException('Anthropic API error: '.$response->body());
        }

        $json = $response->json();
        $textContent = '';
        $parsedToolCalls = [];

        foreach ($json['content'] ?? [] as $block) {
            if ($block['type'] === 'text') {
                $textContent .= $block['text'];
            } elseif ($block['type'] === 'tool_use') {
                $parsedToolCalls[] = [
                    'id' => (string) ($block['id'] ?? uniqid('call_')),
                    'name' => (string) ($block['name'] ?? ''),
                    'arguments' => (array) ($block['input'] ?? []),
                ];
            }
        }

        $promptTokens = (int) ($json['usage']['input_tokens'] ?? 0);
        $completionTokens = (int) ($json['usage']['output_tokens'] ?? 0);
        $tokensUsed = $promptTokens + $completionTokens;

        return [
            'content' => $textContent !== '' ? $textContent : null,
            'tool_calls' => $parsedToolCalls,
            'tokens_used' => $tokensUsed,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
        ];
    }
}
