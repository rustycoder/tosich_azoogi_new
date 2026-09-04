<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
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
            'your-name' => ['required', 'string', 'max:400'],
            'your-email' => ['required', 'email', 'max:400'],
            'your-phone' => ['required', 'string', 'max:400'],
            'your-description' => ['nullable', 'string', 'max:400'],
            'your-products' => ['nullable', 'string', 'max:2000'],
            'radio-choice' => ['nullable', 'string', 'max:400'],
            'contact-choice' => ['nullable', 'string', 'max:400'],
            'suburb-retailer' => ['nullable', 'string', 'max:400'],
        ];
    }
}
