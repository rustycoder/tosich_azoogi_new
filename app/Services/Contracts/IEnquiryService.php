<?php

namespace App\Services\Contracts;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use Illuminate\Support\Collection;

interface IEnquiryService
{
    /**
     * @return array<string, Collection<int, Enquiry>>
     */
    public function kanban(EnquiryType $type, ?EnquiryStatus $status = null): array;

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(EnquiryType $type, array $data): Enquiry;

    public function updateStatus(Enquiry $enquiry, EnquiryStatus $status): Enquiry;

    public function delete(Enquiry $enquiry): void;
}
