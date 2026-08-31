<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\Status;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page && $this->user()?->canManage($page->slug);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::enum(Status::class)],
            'meta' => ['nullable', 'array'],
            'meta.*.value' => ['nullable', 'string'],
            'meta.*.file' => ['nullable', 'file', 'max:12288', 'mimes:jpg,jpeg,png,webp,gif,webm,mp4'],
        ];
    }
}
