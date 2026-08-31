<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'your-company' => ['required', 'string', 'max:400'],
            'your-message' => ['required', 'string', 'max:2000'],
        ];
    }
}
