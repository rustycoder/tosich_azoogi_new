<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Security\MutationGuard;
use Illuminate\Support\Facades\Schema;

class InspectSchemaTool implements McpToolInterface
{
    public function __construct(
        protected MutationGuard $guard
    ) {}

    public function getName(): string
    {
        return 'backend_inspect_schema';
    }

    public function getDescription(): string
    {
        return 'Inspects MySQL database table columns, data types, and primary keys for allowlisted application models.';
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
                    'description' => 'Allowlisted model identifier (e.g., page, pagemeta, product, category, enquiry, project).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        try {
            $modelInstance = $this->guard->resolveModel((string) ($arguments['model'] ?? ''));
            $tableName = $modelInstance->getTable();

            $columns = Schema::getColumnListing($tableName);
            $details = [];

            foreach ($columns as $column) {
                $details[] = [
                    'name' => $column,
                    'type' => Schema::getColumnType($tableName, $column),
                    'is_fillable' => in_array($column, $modelInstance->getFillable(), true),
                ];
            }

            return [
                'isError' => false,
                'content' => json_encode([
                    'table' => $tableName,
                    'model' => get_class($modelInstance),
                    'columns' => $details,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        } catch (\Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Inspect schema failed: '.$e->getMessage(),
            ];
        }
    }
}
