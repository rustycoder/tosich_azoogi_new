<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'airtable_id',
    'name',
    'value',
    'icon',
    'sort_order',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class ProductAttribute extends Model
{
    use Auditable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
