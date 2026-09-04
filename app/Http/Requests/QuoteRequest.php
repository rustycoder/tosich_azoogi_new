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
            'your-name' => ['required', 'string', 'max:191'],
            'your-email' => ['required', 'email', 'max:191'],
            'your-phone' => ['required', 'string', 'max:191'],
            'your-description' => ['nullable', 'string', 'max:400'],
            'your-products' => ['required', 'string', 'max:8000'],
            'radio-choice' => ['required', 'string', 'max:400'],
            'contact-choice' => ['required', 'string', 'max:400'],
            'suburb-retailer' => ['nullable', 'string', 'max:191'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'your-products.required' => 'Add at least one product to your quote before submitting.',
            'radio-choice.required' => 'Please choose which option describes you best.',
            'contact-choice.required' => 'Please choose a preferred contact method.',
        ];
    }
}
