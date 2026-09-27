<?php

declare(strict_types=1);

namespace App\Mcp\Protocol;

use App\Mcp\Contracts\McpToolInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class McpServer
{
    /**
     * @var array<string, McpToolInterface>
     */
    protected array $tools = [];

    /**
     * Register a tool with the server.
     */
    public function registerTool(McpToolInterface $tool): self
    {
        $this->tools[$tool->getName()] = $tool;

        return $this;
    }

    /**
     * Get all registered tools.
     *
     * @return array<string, McpToolInterface>
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * Run the stdio event loop.
     *
     * @param  resource|null  $inputStream
     * @param  resource|null  $outputStream
     */
    public function listen($inputStream = null, $outputStream = null): void
    {
        $stdin = $inputStream ?? fopen('php://stdin', 'r');
        $stdout = $outputStream ?? fopen('php://stdout', 'w');

        while (! feof($stdin)) {
            $line = fgets($stdin);
            if ($line === false || trim($line) === '') {
                continue;
            }

            $request = json_decode($line, true);
            if (! is_array($request)) {
                $this->sendError($stdout, null, -32700, 'Parse error: Invalid JSON');

                continue;
            }

            $response = $this->handleRequest($request);
            if ($response !== null) {
                fwrite($stdout, json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
                fflush($stdout);
            }
        }

        if ($inputStream === null && is_resource($stdin)) {
            fclose($stdin);
        }
        if ($outputStream === null && is_resource($stdout)) {
            fclose($stdout);
        }
    }

    /**
     * Process incoming JSON-RPC request.
     *
     * @param  array<string, mixed>  $req
     * @return array<string, mixed>|null
     */
    public function handleRequest(array $req): ?array
    {
        $id = $req['id'] ?? null;
        $method = (string) ($req['method'] ?? '');
        $params = (array) ($req['params'] ?? []);

        try {
            return match ($method) {
                'initialize' => [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => [
                            'tools' => [
                                'listChanged' => false,
                            ],
                            'resources' => [
                                'subscribe' => false,
                                'listChanged' => false,
                            ],
                        ],
                        'serverInfo' => [
                            'name' => 'laravel-native-mcp',
                            'version' => '1.0.0',
                        ],
                    ],
                ],
                'notifications/initialized', 'initialized' => null,
                'ping' => [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => (object) [],
                ],
                'tools/list' => [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'tools' => array_values(array_map(fn (McpToolInterface $tool) => [
                            'name' => $tool->getName(),
                            'description' => $tool->getDescription(),
                            'inputSchema' => $tool->getInputSchema(),
                        ], $this->tools)),
                    ],
                ],
                'tools/call' => $this->handleToolCall($id, $params),
                default => [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'error' => [
                        'code' => -32601,
                        'message' => "Method not found: {$method}",
                    ],
                ],
            };
        } catch (Throwable $e) {
            Log::error('MCP error: '.$e->getMessage(), ['exception' => $e]);

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error: '.$e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Execute a requested tool.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function handleToolCall(mixed $id, array $params): array
    {
        $toolName = (string) ($params['name'] ?? '');
        $arguments = (array) ($params['arguments'] ?? []);

        if (! isset($this->tools[$toolName])) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => "Tool not found: {$toolName}",
                ],
            ];
        }

        $result = $this->tools[$toolName]->execute($arguments);

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => is_string($result['content'] ?? null)
                            ? $result['content']
                            : json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    ],
                ],
                'isError' => $result['isError'] ?? false,
            ],
        ];
    }

    /**
     * Send direct JSON-RPC error response.
     *
     * @param  resource  $stdout
     */
    protected function sendError($stdout, mixed $id, int $code, string $message): void
    {
        fwrite($stdout, json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ])."\n");
        fflush($stdout);
    }
}
