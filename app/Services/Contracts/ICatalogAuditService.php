<?php

namespace App\Services\Contracts;

interface ICatalogAuditService
{
    /**
     * Run a comprehensive audit of the product, category, and attribute catalog.
     *
     * @return array<string, mixed>
     */
    public function audit(): array;
}
