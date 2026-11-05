<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Models\LlmFeed;
use App\Services\Chat\ChatOrchestrator;
use Illuminate\Support\Str;
use Throwable;

class ManageAiKnowledgeTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'backend_manage_ai_knowledge';
    }

    public function getDescription(): string
    {
        return 'Lists, creates, updates, toggles, or deletes AI System Rules, Company Context items, and FAQ Knowledge Base entries used by the website AI Assistant.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['section', 'action'],
            'properties' => [
                'section' => [
                    'type' => 'string',
                    'enum' => ['rules', 'context', 'faqs'],
                    'description' => 'Target knowledge base section: "rules" (System Rules), "context" (Company Info), or "faqs" (FAQ Knowledge Base).',
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'upsert', 'toggle', 'delete'],
                    'description' => 'Operation to perform.',
                ],
                'id' => [
                    'type' => 'string',
                    'description' => 'Item ID (required for toggle, delete, or updating an existing item).',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Category label (for upsert).',
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'Rule/Context title or FAQ question (for upsert).',
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'Rule/Context body or FAQ answer (for upsert).',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Whether the item is active (default: true).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $section = (string) ($arguments['section'] ?? 'faqs');
        $action = (string) ($arguments['action'] ?? 'list');
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && ! $user->canManagePages()) {
            return [
                'isError' => true,
                'content' => 'You do not have permission to manage AI knowledge base settings.',
            ];
        }

        try {
            [$feedKey, $items] = match ($section) {
                'rules' => ['ai_rules_items', ChatOrchestrator::getRulesItems()],
                'context' => ['ai_context_items', ChatOrchestrator::getContextItems()],
                'faqs' => ['ai_faqs', ChatOrchestrator::getFaqs()],
                default => ['ai_faqs', ChatOrchestrator::getFaqs()],
            };

            if ($action === 'list') {
                return [
                    'isError' => false,
                    'content' => json_encode([
                        'section' => $section,
                        'count' => count($items),
                        'items' => array_values($items),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            $id = trim((string) ($arguments['id'] ?? ''));

            if ($action === 'upsert') {
                $category = trim((string) ($arguments['category'] ?? 'General'));
                $title = trim((string) ($arguments['title'] ?? ''));
                $content = trim((string) ($arguments['content'] ?? ''));
                $isActive = isset($arguments['is_active']) ? (bool) $arguments['is_active'] : true;

                if ($title === '' || $content === '') {
                    return ['isError' => true, 'content' => 'Both title (or question) and content (or answer) are required for upsert.'];
                }

                if ($id === '') {
                    $prefix = match ($section) {
                        'rules' => 'rule_',
                        'context' => 'context_',
                        default => 'faq_',
                    };
                    $id = $prefix.Str::lower(Str::random(8));
                }

                $newItem = $section === 'faqs'
                    ? [
                        'id' => $id,
                        'category' => $category,
                        'question' => $title,
                        'answer' => $content,
                        'is_active' => $isActive,
                    ]
                    : [
                        'id' => $id,
                        'category' => $category,
                        'title' => $title,
                        'content' => $content,
                        'is_active' => $isActive,
                    ];

                $updated = false;
                foreach ($items as $idx => $existing) {
                    if (($existing['id'] ?? '') === $id) {
                        $items[$idx] = $newItem;
                        $updated = true;
                        break;
                    }
                }

                if (! $updated) {
                    $items[] = $newItem;
                }

                $this->persistItems($feedKey, array_values($items));

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => $updated ? 'updated' : 'created',
                        'section' => $section,
                        'item' => $newItem,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'toggle') {
                if ($id === '') {
                    return ['isError' => true, 'content' => 'Item id is required for toggle.'];
                }

                $toggledItem = null;
                foreach ($items as $idx => $existing) {
                    if (($existing['id'] ?? '') === $id) {
                        $items[$idx]['is_active'] = isset($arguments['is_active'])
                            ? (bool) $arguments['is_active']
                            : ! ($existing['is_active'] ?? true);
                        $toggledItem = $items[$idx];
                        break;
                    }
                }

                if (! $toggledItem) {
                    return ['isError' => true, 'content' => "Item '{$id}' not found in {$section}."];
                }

                $this->persistItems($feedKey, array_values($items));

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'toggled',
                        'section' => $section,
                        'item' => $toggledItem,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'delete') {
                if ($id === '') {
                    return ['isError' => true, 'content' => 'Item id is required for delete.'];
                }

                $beforeCount = count($items);
                $items = array_values(array_filter($items, fn (array $item) => ($item['id'] ?? '') !== $id));

                if (count($items) === $beforeCount) {
                    return ['isError' => true, 'content' => "Item '{$id}' not found in {$section}."];
                }

                $this->persistItems($feedKey, $items);

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'deleted',
                        'section' => $section,
                        'deleted_id' => $id,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            return ['isError' => true, 'content' => "Unsupported action: {$action}"];
        } catch (Throwable $e) {
            return [
                'isError' => true,
                'content' => 'AI Knowledge operation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function persistItems(string $feedKey, array $items): void
    {
        LlmFeed::query()->updateOrCreate(
            ['key' => $feedKey],
            [
                'content' => json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'is_custom' => true,
            ]
        );
    }
}
