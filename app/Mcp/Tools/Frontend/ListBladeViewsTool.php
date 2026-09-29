<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Frontend;

use App\Mcp\Contracts\McpToolInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ListBladeViewsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'frontend_list_blade_views';
    }

    public function getDescription(): string
    {
        return 'Lists all Blade templates, layouts, components, and partials in resources/views.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'subdirectory' => [
                    'type' => 'string',
                    'description' => 'Optional subdirectory under resources/views (e.g., "components", "pages", "layouts", "dashboard").',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $baseDir = resource_path('views');
        $targetDir = $baseDir;

        $rawSubdir = $arguments['subdirectory'] ?? null;

        // Auto-unwrap if user pasted a JSON string into a text input box
        if (is_string($rawSubdir) && str_starts_with(trim($rawSubdir), '{')) {
            $decoded = json_decode($rawSubdir, true);
            if (is_array($decoded) && isset($decoded['subdirectory'])) {
                $rawSubdir = $decoded['subdirectory'];
            }
        }

        if (! empty($rawSubdir)) {
            $cleanSubdir = trim(str_replace(['..', "\0"], '', (string) $rawSubdir), '/\\');
            $targetDir = $baseDir.DIRECTORY_SEPARATOR.$cleanSubdir;
        }

        if (! is_dir($targetDir)) {
            return [
                'isError' => true,
                'content' => "Directory not found: {$targetDir}",
            ];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $relativePath = str_replace($baseDir.DIRECTORY_SEPARATOR, '', $file->getPathname());
                $viewDotName = str_replace(['/', '\\', '.blade.php'], ['.', '.', ''], $relativePath);

                $files[] = [
                    'relative_path' => str_replace('\\', '/', $relativePath),
                    'view_name' => $viewDotName,
                    'size_bytes' => $file->getSize(),
                    'last_modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort alphabetically
        usort($files, fn ($a, $b) => strcmp($a['relative_path'], $b['relative_path']));

        return [
            'isError' => false,
            'content' => json_encode(['count' => count($files), 'views' => $files], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }
}
