<?php

namespace Database\Seeders;

use App\Enums\ContentResource;
use App\PageMeta\CatalogSync;
use Illuminate\Database\Seeder;

class ErrorPageSeeder extends Seeder
{
    /**
     * Seed only the error pages (403, 404, 419, 500, 503).
     */
    public function run(): void
    {
        CatalogSync::slugs([
            ContentResource::Forbidden->value,
            ContentResource::NotFound->value,
            ContentResource::PageExpired->value,
            ContentResource::ServerError->value,
            ContentResource::ServiceUnavailable->value,
        ]);
    }
}
