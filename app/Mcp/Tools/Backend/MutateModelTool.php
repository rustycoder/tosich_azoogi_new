<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Security\MutationGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class MutateModelTool implements McpToolInterface
{
    public function __construct(
        protected MutationGuard $guard
    ) {}

    public function getName(): string
    {
        return 'backend_mutate_model';
    }

    public function getDescription(): string
    {
        return 'Executes safe, validated create, update, or delete operations on allowlisted Eloquent models with transaction isolation and dry-run preview capabilities.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['model', 'action'],
            'properties' => [
                'model' => [
                    'type' => 'string',
                    'description' => 'Allowed model identifier (e.g., product, category, pagemeta, project, enquiry, email_template).',
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['create', 'update', 'delete'],
                    'description' => 'The mutation action to perform.',
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => 'Target record ID (required for update and delete).',
                ],
                'attributes' => [
                    'type' => 'object',
                    'description' => 'Key-value pairs of model attributes to create or update.',
                ],
                'dry_run' => [
                    'type' => 'boolean',
                    'description' => 'If true, simulates the mutation in a transaction and rolls it back, returning the diff preview.',
                    'default' => false,
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $modelKey = (string) ($arguments['model'] ?? '');
        $action = (string) ($arguments['action'] ?? '');
        $id = isset($arguments['id']) ? (int) $arguments['id'] : null;
        $rawAttributes = (array) ($arguments['attributes'] ?? []);
        $dryRun = (bool) ($arguments['dry_run'] ?? false);

        try {
            $modelInstance = $this->guard->resolveModel($modelKey);
            $cleanAttributes = $this->guard->sanitizeAttributes($modelInstance, $rawAttributes);

            return DB::transaction(function () use ($modelInstance, $action, $id, $cleanAttributes, $dryRun, $modelKey) {
                $result = match ($action) {
                    'create' => $this->handleCreate($modelInstance, $cleanAttributes),
                    'update' => $this->handleUpdate($modelInstance, $id, $cleanAttributes),
                    'delete' => $this->handleDelete($modelInstance, $id),
                    default => throw new InvalidArgumentException("Unsupported action: {$action}"),
                };

                if ($dryRun) {
                    DB::rollBack();
                    $result['dry_run'] = true;
                    $result['message'] = '[DRY RUN] Mutation simulated successfully without persisting to database.';
                } else {
                    Log::channel('daily')->info("MCP Mutation Executed: {$action} on {$modelKey}", [
                        'record_id' => $id ?? ($result['record']['id'] ?? null),
                        'payload' => $cleanAttributes,
                    ]);
                }

                return [
                    'isError' => false,
                    'content' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            });
        } catch (Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Mutation Failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function handleCreate(Model $model, array $attributes): array
    {
        $record = $model->newInstance($attributes);
        $record->save();

        return [
            'action' => 'create',
            'record' => $record->fresh()?->toArray() ?? $record->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function handleUpdate(Model $model, ?int $id, array $attributes): array
    {
        if (! $id) {
            throw new InvalidArgumentException('Record ID is required for update operations.');
        }

        $record = $model->newQuery()->findOrFail($id);
        $original = $record->only(array_keys($attributes));
        $record->fill($attributes)->save();

        return [
            'action' => 'update',
            'record_id' => $id,
            'original' => $original,
            'updated' => $record->fresh()?->only(array_keys($attributes)) ?? $attributes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function handleDelete(Model $model, ?int $id): array
    {
        if (! $id) {
            throw new InvalidArgumentException('Record ID is required for delete operations.');
        }

        $record = $model->newQuery()->findOrFail($id);
        $record->delete();

        return [
            'action' => 'delete',
            'record_id' => $id,
            'deleted' => true,
        ];
    }
}
