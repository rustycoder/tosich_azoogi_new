<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManage('projects') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'distinct', Rule::exists(Project::class, 'id')],
        ];
    }
}
