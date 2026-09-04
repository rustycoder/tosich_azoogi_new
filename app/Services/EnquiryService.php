<?php

namespace App\Services;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use App\Repositories\Contracts\IEnquiryRepository;
use App\Services\Contracts\IEnquiryService;

class EnquiryService implements IEnquiryService
{
    public function __construct(private IEnquiryRepository $enquiries) {}

    public function kanban(EnquiryType $type, ?EnquiryStatus $status = null): array
    {
        $grouped = $this->enquiries->kanban($type, $status);
        $columns = [];

        foreach (EnquiryStatus::cases() as $column) {
            $columns[$column->value] = $grouped->get($column->value, collect());
        }

        return $columns;
    }

    public function submit(EnquiryType $type, array $data): Enquiry
    {
        return $this->enquiries->create([
            'type' => $type,
            'status' => EnquiryStatus::Pending,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'message' => $data['message'] ?? null,
            'payload' => $data['payload'] ?? [],
        ]);
    }

    public function updateStatus(Enquiry $enquiry, EnquiryStatus $status): Enquiry
    {
        $enquiry->status = $status;
        $this->enquiries->save($enquiry);

        return $enquiry;
    }

    public function delete(Enquiry $enquiry): void
    {
        $this->enquiries->delete($enquiry);
    }
}
