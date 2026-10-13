<?php

namespace App\Services\Contracts;

interface IProductImageService
{
    /**
     * @return array{path: string, mime: string, size: int, etag: string, last_modified: int}|null
     */
    public function deliver(string $sourceUrl, string $format): ?array;
}
