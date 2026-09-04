<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enquiry = $this->route('enquiry');

        return $enquiry instanceof Enquiry
            && ($this->user()?->canManageEnquiryType($enquiry->type) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::enum(EnquiryStatus::class)],
        ];
    }
}
