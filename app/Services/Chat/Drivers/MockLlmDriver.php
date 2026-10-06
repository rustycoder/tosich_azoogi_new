<?php

declare(strict_types=1);

namespace App\Services\Chat\Drivers;

use App\Services\Chat\Contracts\IChatLlmDriver;

class MockLlmDriver implements IChatLlmDriver
{
    public function chat(array $messages, array $tools = [], string $systemPrompt = ''): array
    {
        $lastMessage = end($messages) ?: [];
        $text = strtolower(trim((string) ($lastMessage['content'] ?? '')));

        // If the last message was a tool result, formulate response
        if (($lastMessage['role'] ?? '') === 'tool') {
            return [
                'content' => 'I have processed your request. Here are the relevant product details and options below.',
                'tool_calls' => [],
                'tokens_used' => 50,
            ];
        }

        // Quote Cart Add
        if (str_contains($text, 'add') && (str_contains($text, 'quote') || str_contains($text, 'cart'))) {
            preg_match('/\b\d+\b/', $text, $matches);
            $productId = ! empty($matches[0]) ? (int) $matches[0] : 1;

            return [
                'content' => null,
                'tool_calls' => [
                    [
                        'id' => uniqid('mock_call_'),
                        'name' => 'public_manage_quote_list',
                        'arguments' => ['action' => 'add', 'product_id' => $productId, 'quantity' => 1],
                    ],
                ],
                'tokens_used' => 35,
            ];
        }

        // Submit Enquiry / Quote
        if (str_contains($text, 'submit') || str_contains($text, 'enquiry') || str_contains($text, 'send contact')) {
            return [
                'content' => null,
                'tool_calls' => [
                    [
                        'id' => uniqid('mock_call_'),
                        'name' => 'public_submit_quote_enquiry',
                        'arguments' => [
                            'name' => 'Website Visitor',
                            'email' => 'visitor@example.com',
                            'notes' => 'Inquiry submitted via website AI assistant.',
                        ],
                    ],
                ],
                'tokens_used' => 40,
            ];
        }

        // Custom Datasheet
        if (str_contains($text, 'datasheet') || str_contains($text, 'spec sheet')) {
            return [
                'content' => null,
                'tool_calls' => [
                    [
                        'id' => uniqid('mock_call_'),
                        'name' => 'generate_custom_datasheet',
                        'arguments' => [
                            'product_id' => 1,
                            'product_code' => 'AZ-SAMPLE-01',
                        ],
                    ],
                ],
                'tokens_used' => 40,
            ];
        }

        // Default: Search products
        return [
            'content' => null,
            'tool_calls' => [
                [
                    'id' => uniqid('mock_call_'),
                    'name' => 'public_search_and_filter_products',
                    'arguments' => ['query' => $text ?: 'lighting'],
                ],
            ],
            'tokens_used' => 45,
        ];
    }
}
