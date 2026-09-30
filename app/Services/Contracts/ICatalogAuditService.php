<?php

namespace App\Services\Contracts;

interface ICatalogAuditService
{
    /**
     * Run a comprehensive audit of the product, category, and attribute catalog and persist the report.
     *
     * @return array<string, mixed>
     */
    public function audit(): array;

    /**
     * Get the latest stored audit report without recalculating against the database.
     *
     * @return array<string, mixed>|null
     */
    public function getLatestAudit(): ?array;
}
