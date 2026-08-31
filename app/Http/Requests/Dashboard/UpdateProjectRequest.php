<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        $project = $this->route('project');
        $id = $project instanceof Project ? $project->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($id)],
            'tag' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'completed' => ['nullable', 'string', 'max:100'],
            'cover_remote' => ['nullable', 'string', 'max:500'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'cover_file' => ['nullable', 'image', 'max:8192'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'max:8192'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
        ];
    }
}
