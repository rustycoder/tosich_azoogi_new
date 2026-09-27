<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdatePageContentTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'backend_update_page_content';
    }

    public function getDescription(): string
    {
        return 'Updates page metadata, hero titles, subtitles, banner texts, or custom section values for any page (e.g., solutions, home, about, contact).';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['page_slug', 'meta_updates'],
            'properties' => [
                'page_slug' => [
                    'type' => 'string',
                    'description' => 'The slug or name of the page (e.g. "solutions", "home", "commercial", "about-us").',
                ],
                'meta_updates' => [
                    'type' => 'object',
                    'description' => 'Key-value pairs of page meta keys and their new values (e.g. {"hero.title": "Innovative Lighting Solutions", "hero.lead": "Built for Tomorrow"}).',
                ],
                'dry_run' => [
                    'type' => 'boolean',
                    'description' => 'If true, simulates the update and returns the before/after diff without saving to database.',
                    'default' => false,
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $slug = trim((string) ($arguments['page_slug'] ?? ''));
        $updates = (array) ($arguments['meta_updates'] ?? []);
        $dryRun = (bool) ($arguments['dry_run'] ?? false);

        if ($slug === '' || empty($updates)) {
            return ['isError' => true, 'content' => 'page_slug and meta_updates cannot be empty.'];
        }

        $page = Page::where('slug', $slug)->first()
            ?? Page::where('title', $slug)->first()
            ?? Page::where('title', 'like', "%{$slug}%")->first();

        if (! $page) {
            return ['isError' => true, 'content' => "Page not found with slug or title matching: {$slug}"];
        }

        try {
            return DB::transaction(function () use ($page, $updates, $dryRun) {
                $changed = [];

                foreach ($updates as $inputKey => $value) {
                    $key = (string) $inputKey;

                    // Check exact key first, then fallback to dot notation if underscore passed
                    $meta = PageMeta::where('page_id', $page->id)->where('key', $key)->first();
                    if (! $meta) {
                        $dotKey = str_replace('_', '.', $key);
                        $meta = PageMeta::where('page_id', $page->id)->where('key', $dotKey)->first();
                        if ($meta) {
                            $key = $dotKey;
                        }
                    }

                    if (! $meta) {
                        $meta = new PageMeta([
                            'page_id' => $page->id,
                            'key' => $key,
                            'sort_order' => 0,
                        ]);
                    }

                    $oldValue = $meta->value;
                    $meta->value = (string) $value;

                    if (! $dryRun) {
                        $meta->save();
                    }

                    $changed[$key] = [
                        'before' => $oldValue,
                        'after' => (string) $value,
                    ];
                }

                if ($dryRun) {
                    DB::rollBack();
                } else {
                    Log::channel('daily')->info('Page content updated via MCP', [
                        'page_id' => $page->id,
                        'slug' => $page->slug,
                        'changes' => $changed,
                    ]);
                }

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'page' => $page->title,
                        'slug' => $page->slug,
                        'dry_run' => $dryRun,
                        'updated_fields' => $changed,
                        'message' => $dryRun ? '[DRY RUN] Previewing changes successfully.' : 'Page content successfully updated in MySQL.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            });
        } catch (Throwable $e) {
            return ['isError' => true, 'content' => 'Update failed: '.$e->getMessage()];
        }
    }
}
