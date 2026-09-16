<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'name',
    'description',
    'recipient_emails',
    'subject',
    'body_html',
    'available_tokens',
    'is_active',
])]
class EmailTemplate extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available_tokens' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return list<string>
     */
    public function recipientList(): array
    {
        $raw = (string) $this->recipient_emails;
        $emails = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($emails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderSubject(array $data = []): string
    {
        return $this->interpolate((string) $this->subject, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderBody(array $data = []): string
    {
        return $this->interpolate((string) $this->body_html, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function interpolate(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            $content = str_replace('{{'.$key.'}}', (string) $value, $content);
            $content = str_replace('{{ '.$key.' }}', (string) $value, $content);
        }

        return $content;
    }
}
