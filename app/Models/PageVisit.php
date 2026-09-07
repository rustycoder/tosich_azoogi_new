<?php

namespace App\Models;

use App\Enums\PageVisitKind;
use Database\Factories\PageVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kind', 'page_slug', 'airtable_id', 'country', 'ip_address', 'user_agent'])]
class PageVisit extends Model
{
    /** @use HasFactory<PageVisitFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => PageVisitKind::class,
        ];
    }
}
