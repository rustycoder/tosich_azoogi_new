<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Security\MutationGuard;
use Illuminate\Support\Facades\Schema;

class QueryModelRecordsTool implements McpToolInterface
{
    public function __construct(
        protected MutationGuard $guard
    ) {}

    public function getName(): string
    {
        return 'backend_query_records';
    }

    public function getDescription(): string
    {
        return 'Queries records from allowlisted Eloquent models with safe filtering, ordering, and pagination limit controls.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['model'],
            'properties' => [
                'model' => [
                    'type' => 'string',
                    'description' => 'Target model (e.g., page, pagemeta, product, category, enquiry, project).',
                ],
                'where' => [
                    'type' => 'object',
                    'description' => 'Key-value equality filters (e.g., {"slug": "solutions"}).',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Max records to return (1-50, default: 10).',
                ],
                'order_by' => [
                    'type' => 'string',
                    'description' => 'Column to order by (default: id).',
                ],
                'direction' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                    'description' => 'Sort direction (asc or desc, default: desc).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        try {
            $modelInstance = $this->guard->resolveModel((string) ($arguments['model'] ?? ''));
            $table = $modelInstance->getTable();
            $limit = min(50, max(1, (int) ($arguments['limit'] ?? 10)));
            $where = (array) ($arguments['where'] ?? []);
            $orderBy = (string) ($arguments['order_by'] ?? 'id');
            $direction = strtolower((string) ($arguments['direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

            $validColumns = Schema::getColumnListing($table);

            $query = $modelInstance->newQuery();

            foreach ($where as $col => $val) {
                if (in_array($col, $validColumns, true)) {
                    $query->where($col, $val);
                }
            }

            if (in_array($orderBy, $validColumns, true)) {
                $query->orderBy($orderBy, $direction);
            }

            $records = $query->limit($limit)->get();

            return [
                'isError' => false,
                'content' => json_encode([
                    'count' => $records->count(),
                    'model' => get_class($modelInstance),
                    'records' => $records->toArray(),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        } catch (\Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Query failed: '.$e->getMessage(),
            ];
        }
    }
}
