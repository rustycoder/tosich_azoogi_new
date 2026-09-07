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
}
