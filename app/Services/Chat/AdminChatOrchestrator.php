<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Tools\Backend\AirtableSyncAndAuditTool;
use App\Mcp\Tools\Backend\ChatSessionsInsightTool;
use App\Mcp\Tools\Backend\DashboardAnalyticsTool;
use App\Mcp\Tools\Backend\InspectSchemaTool;
use App\Mcp\Tools\Backend\ManageAiKnowledgeTool;
use App\Mcp\Tools\Backend\MutateModelTool;
use App\Mcp\Tools\Backend\QueryModelRecordsTool;
use App\Mcp\Tools\Backend\SeoAndGeoManagerTool;
use App\Mcp\Tools\Backend\UpdatePageContentTool;
use App\Models\User;
use App\Services\Chat\Adapters\McpChatToolAdapter;
use App\Services\Chat\Contracts\IChatLlmDriver;
use App\Services\Chat\Contracts\IChatTool;
use App\Services\Chat\Drivers\MockLlmDriver;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminChatOrchestrator
{
    /**
     * @var array<string, IChatTool>
     */
    protected array $tools = [];

    protected IChatLlmDriver $driver;

    public function __construct(
        InspectSchemaTool $inspectSchemaTool,
        QueryModelRecordsTool $queryRecordsTool,
        MutateModelTool $mutateModelTool,
        UpdatePageContentTool $updatePageContentTool,
        AirtableSyncAndAuditTool $syncAuditTool,
        ManageAiKnowledgeTool $aiKnowledgeTool,
        ChatSessionsInsightTool $chatSessionsTool,
        DashboardAnalyticsTool $analyticsTool,
        SeoAndGeoManagerTool $seoGeoTool,
    ) {
        $mcpTools = [
            $inspectSchemaTool,
            $queryRecordsTool,
            $mutateModelTool,
            $updatePageContentTool,
            $syncAuditTool,
            $aiKnowledgeTool,
            $chatSessionsTool,
            $analyticsTool,
            $seoGeoTool,
        ];

        foreach ($mcpTools as $mcpTool) {
            $this->registerMcpTool($mcpTool);
        }

        $this->driver = $this->resolveDriver();
    }

    public function registerMcpTool(McpToolInterface $mcpTool): void
    {
        $adapter = new McpChatToolAdapter($mcpTool);
        $this->tools[$adapter->getName()] = $adapter;
    }

    /**
     * @return array<string, IChatTool>
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    protected function resolveDriver(): IChatLlmDriver
    {
        $aiConfig = ChatOrchestrator::getActiveAiConfig();
        $driverName = strtolower((string) ($aiConfig['driver'] ?? config('services.chat.driver', env('CHAT_LLM_DRIVER', 'auto'))));

        if ($driverName !== 'auto') {
            return ChatOrchestrator::makeDriver($driverName);
        }

        if (! empty($aiConfig['anthropic_api_key'])) {
            return ChatOrchestrator::makeDriver('anthropic');
        }

        if (! empty($aiConfig['openrouter_api_key'])) {
            return ChatOrchestrator::makeDriver('openrouter');
        }

        if (! empty($aiConfig['gemini_api_key'])) {
            return ChatOrchestrator::makeDriver('gemini');
        }

        if (! empty($aiConfig['openai_api_key'])) {
            return ChatOrchestrator::makeDriver('openai');
        }

        return app(MockLlmDriver::class);
    }

    /**
     * @return array<int, array{prompt: string, label: string}>
     */
    public static function getStarterChips(): array
    {
        return [
            ['label' => 'Analytics & Pending Enquiries', 'prompt' => 'Show me a summary of pending enquiries, website traffic, and top datasheet downloads'],
            ['label' => 'Airtable Sync & Audit Status', 'prompt' => 'Check the latest Airtable sync status and catalog quality audit'],
            ['label' => 'Unread Customer Chats', 'prompt' => 'List unread customer AI chat sessions'],
            ['label' => 'Audit Page SEO & Sitemap', 'prompt' => 'Run an SEO metadata audit across all pages'],
            ['label' => 'Manage AI FAQs & Rules', 'prompt' => 'List our active AI FAQs and system rules'],
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string, cards: array<int, mixed>, tool_calls: array<int, mixed>, history: array<int, array{role: string, content: string}>}
     */
    public function handleAdminMessage(User $user, string $message, array $history = [], ?string $currentUrl = null): array
    {
        @set_time_limit(120);

        $history = array_slice($history, -10);
        $history[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $llmMessages = $history;
        $systemPrompt = $this->buildSystemPrompt($user, $currentUrl);
        $cardsCollected = [];
        $executedToolCalls = [];

        try {
            $llmResponse = $this->driver->chat($llmMessages, array_values($this->tools), $systemPrompt);

            $rounds = 0;
            while (! empty($llmResponse['tool_calls']) && $rounds < 4) {
                $rounds++;
                $toolResultsForLlm = [];

                foreach ($llmResponse['tool_calls'] as $tc) {
                    $toolName = (string) ($tc['name'] ?? '');
                    $toolArgs = (array) ($tc['arguments'] ?? []);
                    $toolInstance = $this->tools[$toolName] ?? null;

                    if ($toolInstance !== null) {
                        $output = $toolInstance->execute($toolArgs);
                        $executedToolCalls[] = [
                            'id' => $tc['id'],
                            'name' => $toolName,
                            'arguments' => $toolArgs,
                        ];

                        if (! empty($output['cards'])) {
                            $cardsCollected[] = $output['cards'];
                        }

                        $toolResultsForLlm[] = [
                            'role' => 'tool',
                            'tool_call_id' => $tc['id'],
                            'content' => json_encode($output['result'] ?? []),
                        ];
                    }
                }

                if (! empty($llmResponse['raw_message'])) {
                    $llmMessages[] = $llmResponse['raw_message'];
                } else {
                    $llmMessages[] = [
                        'role' => 'assistant',
                        'content' => $llmResponse['content'] ?? '',
                        'tool_calls' => $llmResponse['tool_calls'],
                    ];
                }

                foreach ($toolResultsForLlm as $tr) {
                    $llmMessages[] = $tr;
                }

                $llmResponse = $this->driver->chat($llmMessages, array_values($this->tools), $systemPrompt);
            }

            $reply = (string) ($llmResponse['content'] ?: 'Operation completed. Review the MCP execution card below.');
        } catch (Throwable $e) {
            Log::error('AdminChatOrchestrator error: '.$e->getMessage(), ['exception' => $e]);
            $reply = 'I encountered an error while executing that backend operation: '.$e->getMessage();
        }

        $history[] = [
            'role' => 'assistant',
            'content' => $reply,
        ];

        return [
            'reply' => $reply,
            'cards' => $cardsCollected,
            'tool_calls' => $executedToolCalls,
            'history' => array_slice($history, -12),
        ];
    }

    public function buildSystemPrompt(User $user, ?string $currentUrl = null): string
    {
        $roleLabel = $user->isAdmin() ? 'Administrator (Full Access)' : 'Staff ('.implode(', ', $user->managedResources() ?: ['Limited Access']).')';
        $urlContext = $currentUrl ? "Current Dashboard URL: {$currentUrl}" : '';

        return <<<PROMPT
You are the Azoogi Admin Copilot, an intelligent backend assistant embedded inside the Azoogi Laravel Admin Dashboard.
You have direct access to native Model Context Protocol (MCP) Backend Tools to inspect, audit, and update the application database and content.

Authenticated User: {$user->name} ({$user->email})
Role & Permissions: {$roleLabel}
{$urlContext}

CAPABILITIES & MCP TOOLS:
1. `backend_inspect_schema`: Check database columns and fillable fields for any allowlisted model (`page`, `pagemeta`, `project`, `enquiry`, `email_template`, `datasheet_template`, `llm_feed`). Note: Products, Categories, and Product Attributes are read-only from Airtable and cannot be modified via MCP.
2. `backend_query_records`: Query records from allowlisted models with filtering, sorting, and limits.
3. `backend_update_page_content`: Update page hero titles, leads, SEO metadata, or section copy for any page slug. Supports `dry_run: true` for previewing changes before saving.
4. `backend_mutate_model`: Create, update, or delete records on allowlisted models. Supports `dry_run: true`.
5. `backend_airtable_sync_audit`: Check latest Airtable sync status, run a catalog quality audit, or trigger a live Airtable catalog sync.
6. `backend_manage_ai_knowledge`: List, create, update, toggle, or delete AI System Rules (`rules`), Company Context (`context`), and FAQs (`faqs`).
7. `backend_chat_sessions_insight`: List customer chat logs, read transcripts, mark read/favorite, or convert a chat session into a Quote Enquiry.
8. `backend_dashboard_analytics`: Retrieve pending enquiry counts, website traffic metrics, top visited pages/countries, and PDF datasheet export stats.
9. `backend_seo_and_geo_manager`: Audit pages for missing SEO metadata, regenerate `/sitemap.xml`, or refresh `/llms.txt`.

OPERATIONAL GUIDELINES:
- Be concise, accurate, and helpful. Format responses with clear Markdown bullet points or short summaries.
- When asked to modify or delete critical records and the user asks for a preview (or if the target is ambiguous), use `dry_run: true` first. When the user directly asks to update or change something, execute it and summarize the before/after change clearly.
- Never fabricate database IDs—query or inspect first if you need a record's ID or slug.
PROMPT;
    }
}
