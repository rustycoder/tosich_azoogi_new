<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreDatasheetTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->available_tokens)) {
            $tokens = array_filter(array_map('trim', explode(',', $this->available_tokens)));
            $this->merge(['available_tokens' => array_values($tokens)]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:datasheet_templates,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'body_html' => ['required', 'string'],
            'custom_css' => ['nullable', 'string'],
            'available_tokens' => ['nullable', 'array'],
            'available_tokens.*' => ['string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
