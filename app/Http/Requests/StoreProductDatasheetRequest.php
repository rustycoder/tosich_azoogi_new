<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductDatasheetRequest extends FormRequest
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
            'product_id' => ['required', 'string', 'max:32'],
            'project_name' => ['required', 'string', 'max:191'],
            'person_name' => ['required', 'string', 'max:191'],
            'product_code' => ['nullable', 'string', 'max:191'],
            'selected_options' => ['nullable', 'array', 'max:40'],
            'selected_options.*' => ['nullable', 'string', 'max:191'],
            'length' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_name.required' => 'Enter the project name.',
            'person_name.required' => 'Enter the client name.',
        ];
    }
}
