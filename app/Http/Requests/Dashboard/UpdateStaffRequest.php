<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\ContentResource;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staff = $this->route('staff');
        $id = $staff instanceof User ? $staff->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'resources' => ['nullable', 'array'],
            'resources.*' => ['string', Rule::enum(ContentResource::class)],
        ];
    }
}
