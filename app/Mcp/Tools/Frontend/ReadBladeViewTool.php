<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Frontend;

use App\Mcp\Contracts\McpToolInterface;

class ReadBladeViewTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'frontend_read_blade_view';
    }

    public function getDescription(): string
    {
        return 'Safely reads the content of a Blade view file using either its relative path or dot-notation view name.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['view'],
            'properties' => [
                'view' => [
                    'type' => 'string',
                    'description' => 'The view name (e.g., "pages.products", "layouts.site") or relative path ("pages/products.blade.php").',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $view = trim((string) ($arguments['view'] ?? ''));
        $baseDir = realpath(resource_path('views'));

        if (! $baseDir) {
            return ['isError' => true, 'content' => 'resources/views directory not found.'];
        }

        // Convert dot notation to path if needed
        if (! str_ends_with($view, '.blade.php')) {
            $view = str_replace('.', '/', $view).'.blade.php';
        }

        $targetPath = realpath($baseDir.'/'.ltrim($view, '/\\'));

        // Prevent directory traversal
        if (! $targetPath || ! str_starts_with($targetPath, $baseDir) || ! is_file($targetPath)) {
            return [
                'isError' => true,
                'content' => "View file not found or path is outside resources/views: {$arguments['view']}",
            ];
        }

        $content = file_get_contents($targetPath);

        return [
            'isError' => false,
            'content' => $content !== false ? $content : 'Unable to read file content.',
        ];
    }
}
