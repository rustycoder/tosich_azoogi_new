<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Frontend;

use App\Mcp\Contracts\McpToolInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SearchFrontendAssetsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'frontend_search_assets';
    }

    public function getDescription(): string
    {
        return 'Searches for text, CSS classes, HTML elements, or selectors across frontend files in resources/views and public/assets.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['query'],
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Text or CSS class name to search for (e.g., "hero-banner", ".btn-primary", "lighting").',
                ],
                'scope' => [
                    'type' => 'string',
                    'enum' => ['views', 'assets', 'all'],
                    'description' => 'Scope of search: views (Blade templates), assets (CSS/JS files), or all.',
                    'default' => 'all',
                ],
                'max_matches' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of file matches to return (default 20).',
                    'default' => 20,
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $scope = (string) ($arguments['scope'] ?? 'all');
        $maxMatches = min(50, max(1, (int) ($arguments['max_matches'] ?? 20)));

        if ($query === '') {
            return ['isError' => true, 'content' => 'Query cannot be empty.'];
        }

        $directories = [];
        if (in_array($scope, ['views', 'all'], true) && is_dir(resource_path('views'))) {
            $directories['views'] = resource_path('views');
        }
        if (in_array($scope, ['assets', 'all'], true) && is_dir(public_path('assets'))) {
            $directories['assets'] = public_path('assets');
        }

        $results = [];
        $matchCount = 0;

        foreach ($directories as $scopeName => $dir) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($matchCount >= $maxMatches) {
                    break 2;
                }

                if (! $file->isFile()) {
                    continue;
                }

                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['php', 'css', 'js', 'json', 'svg', 'html'], true)) {
                    continue;
                }

                $content = @file_get_contents($file->getPathname());
                if ($content === false || ! str_contains(strtolower($content), strtolower($query))) {
                    continue;
                }

                // Find matching line numbers
                $lines = explode("\n", $content);
                $fileMatches = [];
                foreach ($lines as $lineNum => $lineContent) {
                    if (str_contains(strtolower($lineContent), strtolower($query))) {
                        $fileMatches[] = [
                            'line' => $lineNum + 1,
                            'content' => trim(mb_substr($lineContent, 0, 150)),
                        ];
                        if (count($fileMatches) >= 5) {
                            break;
                        }
                    }
                }

                $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());

                $results[] = [
                    'file' => str_replace('\\', '/', $relativePath),
                    'matches' => $fileMatches,
                ];

                $matchCount++;
            }
        }

        return [
            'isError' => false,
            'content' => json_encode(['query' => $query, 'total_matched_files' => count($results), 'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }
}
