<?php

declare(strict_types=1);

namespace App\Services\Chat;

class AiCostCalculator
{
    /**
     * Calculate cost in USD given model and token counts.
     */
    public static function calculate(?string $model, int $promptTokens, int $completionTokens): float
    {
        [$inputRate, $outputRate] = self::getRatesForModel($model ?: '');

        $promptCost = ($promptTokens / 1_000_000) * $inputRate;
        $completionCost = ($completionTokens / 1_000_000) * $outputRate;

        return round($promptCost + $completionCost, 6);
    }

    /**
     * @return array{0: float, 1: float} [inputRatePerMillion, outputRatePerMillion] in USD
     */
    public static function getRatesForModel(string $model): array
    {
        $normalized = strtolower(trim($model));

        // Claude family
        if (str_contains($normalized, 'claude-3-5-sonnet') || str_contains($normalized, 'claude-3.5-sonnet')) {
            return [3.00, 15.00];
        }
        if (str_contains($normalized, 'claude-3-5-haiku') || str_contains($normalized, 'claude-3.5-haiku')) {
            return [0.80, 4.00];
        }
        if (str_contains($normalized, 'claude-3-opus') || str_contains($normalized, 'claude-3.0-opus')) {
            return [15.00, 75.00];
        }

        // Gemini family
        if (str_contains($normalized, 'gemini-2.5-flash') || str_contains($normalized, 'gemini-2.0-flash')) {
            return [0.15, 0.60];
        }
        if (str_contains($normalized, 'gemini-2.5-pro') || str_contains($normalized, 'gemini-1.5-pro')) {
            return [1.25, 5.00];
        }
        if (str_contains($normalized, 'gemini-1.5-flash')) {
            return [0.075, 0.30];
        }

        // OpenAI family
        if (str_contains($normalized, 'gpt-4o-mini')) {
            return [0.15, 0.60];
        }
        if (str_contains($normalized, 'gpt-4o')) {
            return [2.50, 10.00];
        }
        if (str_contains($normalized, 'o3-mini')) {
            return [1.10, 4.40];
        }

        // DeepSeek family
        if (str_contains($normalized, 'deepseek-reasoner') || str_contains($normalized, 'deepseek-r1')) {
            return [0.55, 2.19];
        }
        if (str_contains($normalized, 'deepseek')) {
            return [0.14, 0.28];
        }

        // Groq / Llama
        if (str_contains($normalized, 'llama-3.3-70b') || str_contains($normalized, 'llama3.3-70b')) {
            return [0.59, 0.79];
        }

        // Fallback default rate
        return [1.00, 3.00];
    }
}
