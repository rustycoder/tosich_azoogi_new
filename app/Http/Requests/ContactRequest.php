<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
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
            'your-company' => ['required', 'string', 'max:191'],
            'your-message' => ['required', 'string', 'max:2000'],
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
            'cf-turnstile-response.required' => 'Please complete the security check before submitting.',
        ];
    }
}
