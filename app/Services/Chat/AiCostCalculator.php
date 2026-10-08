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

    /**
     * Get complete pricing catalog definition for dashboard displays.
     *
     * @return array<int, array{family: string, pattern: string, provider: string, prompt_rate: float, completion_rate: float, tier: string, context_window: string}>
     */
    public static function getPricingCatalog(): array
    {
        return [
            [
                'family' => 'Claude 3.5 Sonnet',
                'pattern' => 'claude-3-5-sonnet*',
                'provider' => 'Anthropic / OpenRouter',
                'prompt_rate' => 3.00,
                'completion_rate' => 15.00,
                'tier' => 'Flagship Reasoning & Coding',
                'context_window' => '200k',
            ],
            [
                'family' => 'Claude 3.5 Haiku',
                'pattern' => 'claude-3-5-haiku*',
                'provider' => 'Anthropic / OpenRouter',
                'prompt_rate' => 0.80,
                'completion_rate' => 4.00,
                'tier' => 'Fast Lightweight',
                'context_window' => '200k',
            ],
            [
                'family' => 'Claude 3 Opus',
                'pattern' => 'claude-3-opus*',
                'provider' => 'Anthropic',
                'prompt_rate' => 15.00,
                'completion_rate' => 75.00,
                'tier' => 'Deep Complex Reasoning',
                'context_window' => '200k',
            ],
            [
                'family' => 'Google Gemini 2.5 Flash',
                'pattern' => 'gemini-2.5-flash*, gemini-2.0-flash*',
                'provider' => 'Google Gemini',
                'prompt_rate' => 0.15,
                'completion_rate' => 0.60,
                'tier' => 'High Speed Multimodal',
                'context_window' => '1M',
            ],
            [
                'family' => 'Google Gemini 2.5 Pro / 1.5 Pro',
                'pattern' => 'gemini-2.5-pro*, gemini-1.5-pro*',
                'provider' => 'Google Gemini',
                'prompt_rate' => 1.25,
                'completion_rate' => 5.00,
                'tier' => 'Advanced Multimodal Reasoning',
                'context_window' => '2M',
            ],
            [
                'family' => 'Google Gemini 1.5 Flash',
                'pattern' => 'gemini-1.5-flash*',
                'provider' => 'Google Gemini',
                'prompt_rate' => 0.075,
                'completion_rate' => 0.30,
                'tier' => 'Cost-Optimized High Throughput',
                'context_window' => '1M',
            ],
            [
                'family' => 'OpenAI GPT-4o Mini',
                'pattern' => 'gpt-4o-mini*',
                'provider' => 'OpenAI / OpenRouter',
                'prompt_rate' => 0.15,
                'completion_rate' => 0.60,
                'tier' => 'Affordable High Intelligence',
                'context_window' => '128k',
            ],
            [
                'family' => 'OpenAI GPT-4o',
                'pattern' => 'gpt-4o*',
                'provider' => 'OpenAI / OpenRouter',
                'prompt_rate' => 2.50,
                'completion_rate' => 10.00,
                'tier' => 'Flagship Omni Model',
                'context_window' => '128k',
            ],
            [
                'family' => 'OpenAI o3-mini',
                'pattern' => 'o3-mini*',
                'provider' => 'OpenAI',
                'prompt_rate' => 1.10,
                'completion_rate' => 4.40,
                'tier' => 'STEM / Coding Reasoning',
                'context_window' => '200k',
            ],
            [
                'family' => 'DeepSeek Chat (V3)',
                'pattern' => 'deepseek-chat, deepseek*',
                'provider' => 'DeepSeek / OpenRouter',
                'prompt_rate' => 0.14,
                'completion_rate' => 0.28,
                'tier' => 'Ultra-low Cost General LLM',
                'context_window' => '64k',
            ],
            [
                'family' => 'DeepSeek Reasoner (R1)',
                'pattern' => 'deepseek-reasoner, deepseek-r1',
                'provider' => 'DeepSeek / OpenRouter',
                'prompt_rate' => 0.55,
                'completion_rate' => 2.19,
                'tier' => 'Open Reasoning Chain',
                'context_window' => '64k',
            ],
            [
                'family' => 'Groq Llama 3.3 70B',
                'pattern' => 'llama-3.3-70b*',
                'provider' => 'Groq / Meta',
                'prompt_rate' => 0.59,
                'completion_rate' => 0.79,
                'tier' => 'Ultra-fast LPU Inference',
                'context_window' => '128k',
            ],
            [
                'family' => 'Custom / Default Fallback',
                'pattern' => 'unmatched models',
                'provider' => 'Default Fallback',
                'prompt_rate' => 1.00,
                'completion_rate' => 3.00,
                'tier' => 'Fallback Conservative Rate',
                'context_window' => '—',
            ],
        ];
    }
}
