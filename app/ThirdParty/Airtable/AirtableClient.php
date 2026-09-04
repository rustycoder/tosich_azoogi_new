<?php

namespace App\ThirdParty\Airtable;

use App\ThirdParty\Airtable\Contracts\IAirtableClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AirtableClient implements IAirtableClient
{
    public function fetchRecords(string $table, ?string $sortField = 'Order'): array
    {
        $records = [];

        foreach ($this->eachPage($table, $sortField) as $page) {
            foreach ($page as $record) {
                $records[] = $record;
            }
        }

        return $records;
    }

    public function eachPage(string $table, ?string $sortField = 'Order'): iterable
    {
        $baseId = (string) config('airtable.base_id');
        $apiKey = (string) config('airtable.api_key');

        if ($baseId === '' || $apiKey === '') {
            throw new RuntimeException('Airtable API key and base ID must be set.');
        }

        $yielded = false;

        try {
            foreach ($this->iteratePages($baseId, $apiKey, $table, $sortField) as $page) {
                $yielded = true;
                yield $page;
            }
        } catch (RuntimeException $exception) {
            if ($yielded || $sortField === null) {
                throw $exception;
            }

            yield from $this->iteratePages($baseId, $apiKey, $table, null);
        }
    }

    /**
     * @return iterable<int, list<array{id: string, fields: array<string, mixed>}>>
     */
    private function iteratePages(string $baseId, string $apiKey, string $table, ?string $sortField): iterable
    {
        $offset = null;

        do {
            $query = [];

            if ($offset !== null) {
                $query['offset'] = $offset;
            }

            if ($sortField !== null && $sortField !== '') {
                $query['sort'] = [['field' => $sortField, 'direction' => 'asc']];
            }

            $response = $this->request($apiKey, $baseId, $table, $query);

            if (! $response->successful()) {
                throw new RuntimeException(
                    'Airtable request failed ('.$response->status().'): '.$response->body(),
                );
            }

            $data = $response->json();
            $fetched = $data['records'] ?? [];
            $page = [];

            if (is_array($fetched)) {
                foreach ($fetched as $record) {
                    if (is_array($record) && isset($record['id'])) {
                        $page[] = [
                            'id' => (string) $record['id'],
                            'fields' => is_array($record['fields'] ?? null) ? $record['fields'] : [],
                        ];
                    }
                }
            }

            yield $page;

            $offset = is_string($data['offset'] ?? null) ? $data['offset'] : null;
        } while ($offset !== null);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function request(string $apiKey, string $baseId, string $table, array $query): Response
    {
        $url = 'https://api.airtable.com/v0/'.$baseId.'/'.rawurlencode($table);
        $backoff = 1.5;

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(60)
                ->get($url, $query);

            if ($response->status() !== 429) {
                return $response;
            }

            usleep((int) ($backoff * 1_000_000));
            $backoff *= 2;
        }

        return $response;
    }
}
