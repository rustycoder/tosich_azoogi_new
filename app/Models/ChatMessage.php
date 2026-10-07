<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chat_session_id',
    'sender',
    'content',
    'tool_calls',
    'tool_results',
    'cards_payload',
    'tokens_used',
    'prompt_tokens',
    'completion_tokens',
    'model',
    'driver',
    'estimated_cost',
])]
class ChatMessage extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tool_calls' => 'array',
            'tool_results' => 'array',
            'cards_payload' => 'array',
            'tokens_used' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'estimated_cost' => 'decimal:6',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function isUser(): bool
    {
        return $this->sender === 'user';
    }

    public function isAssistant(): bool
    {
        return $this->sender === 'assistant';
    }
}
