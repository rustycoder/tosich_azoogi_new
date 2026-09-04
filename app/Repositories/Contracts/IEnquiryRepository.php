<?php

namespace App\Repositories\Contracts;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use Illuminate\Support\Collection;

interface IEnquiryRepository
{
    /**
     * @return Collection<string, Collection<int, Enquiry>>
     */
    public function kanban(EnquiryType $type, ?EnquiryStatus $status = null): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Enquiry;

    public function save(Enquiry $enquiry): void;

    public function delete(Enquiry $enquiry): void;
}
