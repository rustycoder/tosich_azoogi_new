<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Frontend;

use App\Mcp\Contracts\McpToolInterface;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

class ListRoutesTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'frontend_list_routes';
    }

    public function getDescription(): string
    {
        return 'Lists registered Laravel application routes with method, URI, action, name, and middleware filters.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'method' => [
                    'type' => 'string',
                    'description' => 'Filter by HTTP method (GET, POST, PUT, DELETE).',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Keyword to match against URI or route name.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $methodFilter = strtoupper(trim((string) ($arguments['method'] ?? '')));
        $search = strtolower(trim((string) ($arguments['search'] ?? '')));

        $routes = [];
        /** @var Route $route */
        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            $methods = array_values(array_filter($route->methods(), fn ($m) => $m !== 'HEAD'));
            $uri = $route->uri();
            $name = $route->getName() ?? '';
            $action = $route->getActionName();

            if ($methodFilter !== '' && ! in_array($methodFilter, $methods, true)) {
                continue;
            }

            if ($search !== '' && ! str_contains(strtolower($uri), $search) && ! str_contains(strtolower($name), $search)) {
                continue;
            }

            $routes[] = [
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'action' => $action,
                'middleware' => $route->middleware(),
            ];
        }

        return [
            'isError' => false,
            'content' => json_encode(['count' => count($routes), 'routes' => $routes], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }
}
