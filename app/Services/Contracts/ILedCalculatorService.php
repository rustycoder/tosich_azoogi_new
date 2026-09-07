<?php

namespace App\Services\Contracts;

interface ILedCalculatorService
{
    /**
     * @return array{lights: list<array<string, mixed>>, drivers: list<array<string, mixed>>, controllers: list<array<string, mixed>>}
     */
    public function catalog(): array;
}
