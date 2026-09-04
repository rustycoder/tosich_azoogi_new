<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductEnquiryRequest extends FormRequest
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
            'quote-name' => ['required', 'string', 'max:191'],
            'quote-email' => ['required', 'email', 'max:191'],
            'quote-company' => ['required', 'string', 'max:191'],
            'quote-project' => ['required', 'string', 'max:191'],
            'quote-spec' => ['required', 'string', 'max:8000'],
            'quote-message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quote-spec.required' => 'Configure the product before sending this enquiry.',
        ];
    }
}
