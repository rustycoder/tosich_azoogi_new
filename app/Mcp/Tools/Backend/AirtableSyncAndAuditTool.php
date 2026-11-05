<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Services\Contracts\ICatalogAuditService;
use App\Services\Contracts\IProductSyncService;
use Throwable;

class AirtableSyncAndAuditTool implements McpToolInterface
{
    public function __construct(
        protected IProductSyncService $syncService,
        protected ICatalogAuditService $auditService,
    ) {}

    public function getName(): string
    {
        return 'backend_airtable_sync_audit';
    }

    public function getDescription(): string
    {
        return 'Checks Airtable product sync status, runs or retrieves the catalog quality & WebP/SVG asset audit, or triggers a live Airtable catalog synchronization.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['action'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['status', 'run_audit', 'trigger_sync'],
                    'description' => 'Action to execute: "status" (view latest sync and audit summary), "run_audit" (re-evaluate catalog quality), or "trigger_sync" (pull fresh records from Airtable).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $action = (string) ($arguments['action'] ?? 'status');
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && ! $user->canManageProducts()) {
            return [
                'isError' => true,
                'content' => 'You do not have permission to manage product synchronization or catalog audits.',
            ];
        }

        try {
            if ($action === 'trigger_sync') {
                $triggeredBy = $user ? 'mcp:'.$user->name : 'mcp';
                $syncRecord = $this->syncService->sync(triggeredBy: $triggeredBy, force: true);
                $audit = $this->auditService->getLatestAudit() ?? $this->auditService->audit();

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'trigger_sync',
                        'message' => 'Airtable catalog synchronized and audited successfully.',
                        'sync' => [
                            'id' => $syncRecord->id,
                            'status' => $syncRecord->status,
                            'synced_count' => $syncRecord->synced_count,
                            'triggered_by' => $syncRecord->triggered_by,
                            'completed_at' => $syncRecord->completed_at?->toIso8601String(),
                        ],
                        'audit_summary' => $this->summarizeAudit($audit),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'run_audit') {
                $audit = $this->auditService->audit();

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'run_audit',
                        'message' => 'Catalog quality, WebP image, and SVG technical icon audit completed.',
                        'audit_summary' => $this->summarizeAudit($audit),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            $latestSync = $this->syncService->latestSync();
            $audit = $this->auditService->getLatestAudit() ?? $this->auditService->audit();

            return [
                'isError' => false,
                'content' => json_encode([
                    'action' => 'status',
                    'latest_sync' => $latestSync ? [
                        'id' => $latestSync->id,
                        'status' => $latestSync->status,
                        'synced_count' => $latestSync->synced_count,
                        'triggered_by' => $latestSync->triggered_by,
                        'started_at' => $latestSync->started_at?->toIso8601String(),
                        'completed_at' => $latestSync->completed_at?->toIso8601String(),
                        'error' => $latestSync->error,
                    ] : null,
                    'audit_summary' => $this->summarizeAudit($audit),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        } catch (Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Airtable sync/audit operation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>|null  $audit
     * @return array<string, mixed>
     */
    protected function summarizeAudit(?array $audit): array
    {
        if (! $audit) {
            return ['status' => 'no_audit_available'];
        }

        return [
            'generated_at' => $audit['generated_at'] ?? null,
            'totals' => $audit['totals'] ?? [],
            'scores' => $audit['scores'] ?? [],
            'issues_count' => isset($audit['issues']) && is_array($audit['issues']) ? array_map('count', $audit['issues']) : [],
        ];
    }
}
