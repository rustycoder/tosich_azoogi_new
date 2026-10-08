<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid',
    'ip_address',
    'country',
    'user_agent',
    'referrer_url',
    'lead_name',
    'lead_email',
    'lead_phone',
    'lead_company',
    'project_name',
    'enquiry_id',
    'messages_count',
    'total_tokens',
    'total_cost',
    'primary_model',
    'status',
    'is_read',
    'is_favorite',
    'summary',
    'metadata',
])]
class ChatSession extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'messages_count' => 'integer',
            'total_tokens' => 'integer',
            'total_cost' => 'decimal:6',
            'is_read' => 'boolean',
            'is_favorite' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id', 'asc');
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function hasLead(): bool
    {
        return ! empty($this->lead_email) || ! empty($this->lead_name) || ! empty($this->project_name) || $this->enquiry_id !== null;
    }
}
