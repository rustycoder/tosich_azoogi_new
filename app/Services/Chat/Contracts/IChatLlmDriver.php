<?php

declare(strict_types=1);

namespace App\Services\Chat\Contracts;

interface IChatLlmDriver
{
    /**
     * Send messages and tool definitions to the LLM and receive the response (content and/or tool_calls).
     *
     * @param  array<int, array{role: string, content: string, tool_calls?: array<mixed>, tool_call_id?: string}>  $messages
     * @param  array<int, IChatTool>  $tools
     * @return array{content: string|null, tool_calls: array<int, array{id: string, name: string, arguments: array<string, mixed>}>, tokens_used?: int}
     */
    public function chat(array $messages, array $tools = [], string $systemPrompt = ''): array;
}
