<?php

namespace App\Models;

use Database\Factories\ProductDatasheetExportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'product_id',
    'airtable_id',
    'product_code',
    'product_name',
    'project_name',
    'person_name',
    'configuration',
    'ip_address',
    'country',
    'user_agent',
])]
class ProductDatasheetExport extends Model
{
    /** @use HasFactory<ProductDatasheetExportFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function listingTitle(): string
    {
        if (filled($this->product_code)) {
            return (string) $this->product_code;
        }

        return filled($this->product_name) ? (string) $this->product_name : '—';
    }

    public function listingImageUrl(): string
    {
        $product = $this->product;

        if ($product !== null) {
            $images = $product->product_images;

            if (is_array($images) && $images !== []) {
                $first = $images[0];

                if (is_string($first) && $first !== '') {
                    return media_url($first);
                }
            }

            $cover = media_url((string) ($product->cover ?? ''));

            if ($cover !== '') {
                return $cover;
            }
        }

        $configuration = is_array($this->configuration) ? $this->configuration : [];

        return media_url((string) ($configuration['product_image'] ?? ''));
    }

    public function listingDescription(): string
    {
        if (! filled($this->product_name) || $this->product_name === $this->product_code) {
            return '';
        }

        return (string) $this->product_name;
    }

    public function listingDialogTitle(): string
    {
        $title = $this->listingTitle();

        return $title === '—' ? 'Export details' : $title;
    }

    public function listingDialogSubtitle(): string
    {
        return $this->listingDescription();
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function listingInfoRows(): array
    {
        $country = country_name($this->country);
        $device = device_name($this->user_agent);

        return [
            ['label' => 'Client name', 'value' => filled($this->person_name) ? (string) $this->person_name : '—'],
            ['label' => 'Project name', 'value' => filled($this->project_name) ? (string) $this->project_name : '—'],
            ['label' => 'Country', 'value' => $country !== '' ? $country : '—'],
            ['label' => 'IP', 'value' => filled($this->ip_address) ? (string) $this->ip_address : '—'],
            ['label' => 'Device', 'value' => $device !== '' ? $device : '—'],
        ];
    }
}
