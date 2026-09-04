<?php

namespace App\ThirdParty\Airtable\Contracts;

interface IAirtableClient
{
    /**
     * @return list<array{id: string, fields: array<string, mixed>}>
     */
    public function fetchRecords(string $table, ?string $sortField = 'Order'): array;

    /**
     * @return iterable<int, list<array{id: string, fields: array<string, mixed>}>>
     */
    public function eachPage(string $table, ?string $sortField = 'Order'): iterable;
}
