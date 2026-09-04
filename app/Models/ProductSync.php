<?php

namespace App\Models;

use App\Enums\ProductSyncStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'status',
    'products_count',
    'started_at',
    'finished_at',
    'error',
    'triggered_by',
    'created_by',
    'updated_by',
])]
class ProductSync extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductSyncStatus::class,
            'products_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function isRunning(): bool
    {
        return $this->status === ProductSyncStatus::Running;
    }
}
