<?php

declare(strict_types=1);

namespace App\Services\Chat\Adapters;

use App\Mcp\Contracts\McpToolInterface;
use App\Services\Chat\Contracts\IChatTool;

class McpChatToolAdapter implements IChatTool
{
    public function __construct(
        protected McpToolInterface $mcpTool,
    ) {}

    public function getName(): string
    {
        return $this->mcpTool->getName();
    }

    public function getDescription(): string
    {
        return $this->mcpTool->getDescription();
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        $schema = $this->mcpTool->getInputSchema();
        if (empty($schema['properties'])) {
            $schema['properties'] = (object) [];
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(array $arguments): array
    {
        $raw = $this->mcpTool->execute($arguments);
        $isError = (bool) ($raw['isError'] ?? false);
        $contentText = (string) ($raw['content'] ?? '');

        $decoded = json_decode($contentText, true);
        $structured = is_array($decoded) ? $decoded : [
            'status' => $isError ? 'error' : 'success',
            'message' => $contentText,
        ];

        if ($isError && ! isset($structured['error'])) {
            $structured['error'] = $contentText;
        }

        return [
            'result' => $structured,
            'cards' => [
                'type' => 'mcp_tool_execution',
                'tool' => $this->mcpTool->getName(),
                'is_error' => $isError,
                'arguments' => $arguments,
                'summary' => $this->buildSummaryLabel($this->mcpTool->getName(), $arguments, $structured, $isError),
                'payload' => $structured,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $structured
     */
    protected function buildSummaryLabel(string $toolName, array $arguments, array $structured, bool $isError): string
    {
        if ($isError) {
            return (string) ($structured['error'] ?? $structured['message'] ?? 'Operation failed');
        }

        return match ($toolName) {
            'backend_inspect_schema' => 'Inspected schema for '.($structured['table'] ?? ($arguments['model'] ?? 'model')),
            'backend_query_records' => 'Found '.($structured['count'] ?? 0).' '.($arguments['model'] ?? 'record').'(s)',
            'backend_mutate_model' => (! empty($structured['dry_run']) ? '[Dry Run] ' : '').ucfirst((string) ($arguments['action'] ?? 'Mutated')).' '.($arguments['model'] ?? 'model'),
            'backend_update_page_content' => (! empty($structured['dry_run']) ? '[Dry Run] ' : '').'Updated page content for '.($structured['slug'] ?? ($arguments['page_slug'] ?? 'page')),
            'backend_airtable_sync_audit' => (string) ($structured['message'] ?? 'Checked Airtable sync & catalog audit'),
            'backend_manage_ai_knowledge' => ucfirst((string) ($structured['action'] ?? $arguments['action'] ?? 'Managed')).' AI '.($arguments['section'] ?? 'knowledge'),
            'backend_chat_sessions_insight' => 'Inspected customer chat sessions',
            'backend_dashboard_analytics' => 'Loaded dashboard analytics summary',
            'backend_seo_and_geo_manager' => (string) ($structured['message'] ?? 'Completed SEO & GEO audit'),
            default => 'Executed '.$toolName,
        };
    }
}
