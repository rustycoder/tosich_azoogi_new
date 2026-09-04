<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Concerns\Auditable;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'type',
    'status',
    'name',
    'email',
    'phone',
    'company',
    'message',
    'payload',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EnquiryType::class,
            'status' => EnquiryStatus::class,
            'payload' => 'array',
        ];
    }

    public function preview(): string
    {
        $payload = $this->payload ?? [];
        $fromPayload = $payload['products'] ?? $payload['specification'] ?? $payload['project'] ?? '';

        $text = trim((string) ($this->message ?: $fromPayload));

        return $text === '' ? '—' : Str::limit($text, 140);
    }

    /**
     * @return list<array{label: string, value: string, href: ?string, wide: bool}>
     */
    public function detailRows(): array
    {
        $payload = $this->payload ?? [];
        $rows = [];

        if (filled($this->email)) {
            $rows[] = [
                'label' => 'Email',
                'value' => $this->email,
                'href' => 'mailto:'.$this->email,
                'wide' => false,
            ];
        }

        if (filled($this->phone)) {
            $phone = (string) $this->phone;
            $tel = preg_replace('/[^\d+]/', '', $phone) ?: null;

            $rows[] = [
                'label' => 'Phone',
                'value' => $phone,
                'href' => $tel ? 'tel:'.$tel : null,
                'wide' => false,
            ];
        }

        if (filled($this->company)) {
            $rows[] = $this->detailRow('Company', (string) $this->company);
        }

        foreach ([
            'project' => 'Project',
            'role' => 'Role',
            'method' => 'Contact method',
            'suburb' => 'Suburb or retailer',
        ] as $key => $label) {
            $value = trim((string) ($payload[$key] ?? ''));
            if ($value !== '') {
                $rows[] = $this->detailRow($label, $value);
            }
        }

        foreach ([
            'products' => 'Products',
            'specification' => 'Specification',
            'description' => 'Description',
        ] as $key => $label) {
            $value = trim((string) ($payload[$key] ?? ''));
            if ($value !== '') {
                $rows[] = $this->detailRow($label, $value, wide: true);
            }
        }

        if (filled($this->message) && trim((string) ($payload['description'] ?? '')) !== trim((string) $this->message)) {
            $rows[] = $this->detailRow('Message', (string) $this->message, wide: true);
        }

        return $rows;
    }

    /**
     * @return array{label: string, value: string, href: ?string, wide: bool}
     */
    private function detailRow(string $label, string $value, bool $wide = false): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'href' => null,
            'wide' => $wide,
        ];
    }
}
