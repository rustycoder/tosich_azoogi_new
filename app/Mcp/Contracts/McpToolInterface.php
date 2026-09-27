<?php

declare(strict_types=1);

namespace App\Mcp\Contracts;

interface McpToolInterface
{
    /**
     * Unique tool identifier.
     */
    public function getName(): string;

    /**
     * Human-readable description for LLM capability discovery.
     */
    public function getDescription(): string;

    /**
     * JSON Schema defining expected input arguments.
     *
     * @return array<string, mixed>
     */
    public function getInputSchema(): array;

    /**
     * Execute the tool with validated arguments.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(array $arguments): array;
}
