<?php

declare(strict_types=1);

namespace App\Services\Chat\Contracts;

interface IChatTool
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    /**
     * Execute the tool with given arguments.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed> Returns array with 'result' (data for LLM) and optional 'cards' (UI component payloads)
     */
    public function execute(array $arguments): array;
}
