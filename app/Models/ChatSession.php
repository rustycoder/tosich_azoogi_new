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
    'enquiry_id',
    'messages_count',
    'status',
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
            'metadata' => 'array',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function hasLead(): bool
    {
        return ! empty($this->lead_email) || ! empty($this->lead_name) || $this->enquiry_id !== null;
    }
}
