<?php

namespace App\Repositories;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use App\Repositories\Contracts\IEnquiryRepository;
use Illuminate\Support\Collection;

class EnquiryRepository implements IEnquiryRepository
{
    public function kanban(EnquiryType $type, ?EnquiryStatus $status = null): Collection
    {
        return Enquiry::query()
            ->with('updater:id,name')
            ->where('type', $type)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get()
            ->groupBy(fn (Enquiry $enquiry): string => $enquiry->status->value);
    }

    public function create(array $data): Enquiry
    {
        return Enquiry::query()->create($data);
    }

    public function save(Enquiry $enquiry): void
    {
        $enquiry->save();
    }

    public function delete(Enquiry $enquiry): void
    {
        $enquiry->delete();
    }
}
