<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
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
            'cf-turnstile-response' => [
                Rule::when((bool) config('services.turnstile.enabled'), ['required', 'string', new Turnstile]),
            ],
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
            'cf-turnstile-response.required' => 'Please complete the security check before submitting.',
        ];
    }
}
