<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductEnquiryRequest extends FormRequest
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
            'quote-name' => ['required', 'string', 'max:191'],
            'quote-email' => ['required', 'email', 'max:191'],
            'quote-company' => ['required', 'string', 'max:191'],
            'quote-project' => ['required', 'string', 'max:191'],
            'quote-spec' => ['required', 'string', 'max:8000'],
            'quote-message' => ['nullable', 'string', 'max:2000'],
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
            'quote-spec.required' => 'Configure the product before sending this enquiry.',
            'cf-turnstile-response.required' => 'Please complete the security check before sending this enquiry.',
        ];
    }
}
