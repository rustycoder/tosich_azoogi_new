<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['nullable', 'array', 'max:50'],
            'ids.*' => ['required', 'string', 'max:191'],
        ];
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        $ids = $this->validated('ids') ?? [];

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn (mixed $id): string => trim((string) $id),
            $ids,
        ), fn (string $id): bool => $id !== '')));
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids');

        if (is_string($ids)) {
            $this->merge([
                'ids' => array_values(array_filter(array_map('trim', explode(',', $ids)))),
            ]);
        }
    }
}
