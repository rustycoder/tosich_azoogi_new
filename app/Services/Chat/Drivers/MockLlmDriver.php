<?php

declare(strict_types=1);

namespace App\Services\Chat\Drivers;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatLlmDriver;

class MockLlmDriver implements IChatLlmDriver
{
    public function chat(array $messages, array $tools = [], string $systemPrompt = ''): array
    {
        $lastMessage = end($messages) ?: [];
        $role = $lastMessage['role'] ?? '';
        $text = strtolower(trim((string) ($lastMessage['content'] ?? '')));

        // 1. Formulate intelligent response if the last message was a tool result
        if ($role === 'tool') {
            $toolResult = json_decode((string) ($lastMessage['content'] ?? '{}'), true) ?: [];

            // Case A: Product Search Results
            if (isset($toolResult['products'])) {
                $count = (int) ($toolResult['matched_count'] ?? count($toolResult['products']));
                if ($count > 0) {
                    $reply = "I found {$count} lighting fixture(s) matching your request. You can explore their technical specifications, download datasheets, or add them directly to your quote request below.";
                } else {
                    $reply = "We don't have an exact stock match for those specific dimensions or criteria, but Azoogi provides custom architectural linear profiles, custom cut lengths, and bespoke lighting engineering. Here are our closest architectural fixtures:";
                }

                return [
                    'content' => $reply,
                    'tool_calls' => [],
                    'tokens_used' => 50,
                ];
            }

            // Case B: Product Details
            if (isset($toolResult['name'])) {
                $name = $toolResult['name'];
                $code = $toolResult['code'] ?? '';
                $reply = "Here are the technical specifications, photometric files, and installation guides for {$name} ({$code}).";

                return [
                    'content' => $reply,
                    'tool_calls' => [],
                    'tokens_used' => 45,
                ];
            }

            // Case C: Custom Datasheet
            if (isset($toolResult['export_uuid']) || isset($toolResult['download_url'])) {
                $name = $toolResult['product_name'] ?? 'your selected fixture';
                $reply = "Your custom PDF datasheet for {$name} has been generated. You can download and save the document using the card below.";

                return [
                    'content' => $reply,
                    'tool_calls' => [],
                    'tokens_used' => 40,
                ];
            }

            // Case D: Quote Cart Management
            if (isset($toolResult['quote_items']) || isset($toolResult['total_items'])) {
                $total = (int) ($toolResult['total_items'] ?? count($toolResult['quote_items'] ?? []));
                if ($total === 0) {
                    $reply = "Your quote request list is currently empty. You can add fixtures by clicking '+ Add to Quote' on any product card or by asking me to add specific products.";
                } else {
                    $msg = $toolResult['message'] ?? "You currently have {$total} item(s) in your quote request list.";
                    $reply = $msg.' You can review your items or proceed to submit your quote request.';
                }

                return [
                    'content' => $reply,
                    'tool_calls' => [],
                    'tokens_used' => 35,
                ];
            }

            // Case E: Lead Enquiry Submission
            if (isset($toolResult['enquiry_id']) || isset($toolResult['lead_id'])) {
                $ref = $toolResult['enquiry_id'] ?? rand(1000, 9999);

                return [
                    'content' => "Thank you! Your quote enquiry has been submitted to our Sydney sales engineering team (Ref #{$ref}). A lighting specialist will review your project requirements and be in touch shortly.",
                    'tool_calls' => [],
                    'tokens_used' => 40,
                ];
            }

            return [
                'content' => 'Here are the relevant product details and options matching your request below.',
                'tool_calls' => [],
                'tokens_used' => 30,
            ];
        }

        // 2. Intent Analysis for User Messages

        // Intent: Informational / How-To Questions on Datasheets
        if (preg_match('/\b(how|where|can i|explain|guide|help)\b/i', $text) && (str_contains($text, 'datasheet') || str_contains($text, 'spec sheet') || str_contains($text, 'pdf'))) {
            return [
                'content' => "You can generate custom PDF datasheets in two easy ways:\n\n1. **Product Pages**: Navigate to any product in our catalog, configure your custom options (e.g. CCT, beam angle, finish, or custom cut lengths), and click **\"Download Custom Datasheet\"**.\n2. **Direct in Chat**: Ask me directly (e.g., *\"Generate a datasheet for 12W Garden Light\"* or *\"Create a spec sheet for Lumoflex\"*), and I will generate and provide an instant download link card.",
                'tool_calls' => [],
                'tokens_used' => 60,
            ];
        }

        // Intent: Informational / How-To Questions on Quote List
        if (preg_match('/\b(how|where|can i|explain|guide|help)\b/i', $text) && str_contains($text, 'quote')) {
            return [
                'content' => "You can add products to your quote list in two convenient ways:\n\n1. **Product Cards**: Click the **\"+ Add to Quote\"** button on any product card in the catalog or right inside this chat.\n2. **Direct in Chat**: Ask me directly (e.g., *\"Add 12W Garden Light to my quote\"* or *\"Show my quote list\"*).\n\nWhen you're ready, ask me to submit your quote and provide your email to receive project pricing and lead times from our Sydney engineering team!",
                'tool_calls' => [],
                'tokens_used' => 60,
            ];
        }

        // Intent: Quote Cart Actions
        if (str_contains($text, 'quote') && (str_contains($text, 'add') || str_contains($text, 'cart') || str_contains($text, 'item') || str_contains($text, 'list') || str_contains($text, 'remove') || str_contains($text, 'clear') || str_contains($text, 'show') || str_contains($text, 'view'))) {
            $action = 'view';
            if (str_contains($text, 'remove') || str_contains($text, 'delete')) {
                $action = 'remove';
            } elseif (str_contains($text, 'clear') || str_contains($text, 'empty')) {
                $action = 'clear';
            } elseif (str_contains($text, 'add')) {
                $action = 'add';
            }

            $productId = null;
            if ($action === 'add' || $action === 'remove') {
                preg_match('/\b\d+\b/', $text, $matches);
                $productId = ! empty($matches[0]) ? (int) $matches[0] : null;

                if (! $productId) {
                    $matchedProduct = Product::where('status', 'publish')
                        ->where(function ($q) use ($text) {
                            $q->whereRaw('? LIKE CONCAT("%", LOWER(product_name), "%")', [$text])
                                ->orWhereRaw('? LIKE CONCAT("%", LOWER(product_code), "%")', [$text]);
                        })->first();
                    $productId = $matchedProduct ? $matchedProduct->id : null;
                }
            }

            return [
                'content' => null,
                'tool_calls' => [
                    [
                        'id' => uniqid('mock_call_'),
                        'name' => 'public_manage_quote_list',
                        'arguments' => ['action' => $action, 'product_id' => $productId, 'quantity' => 1],
                    ],
                ],
                'tokens_used' => 35,
            ];
        }

        // Intent: Submit Enquiry / Quote
        if (str_contains($text, 'submit') || str_contains($text, 'enquiry') || str_contains($text, 'send contact') || str_contains($text, 'contact sales') || str_contains($text, 'get a quote')) {
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

        // Intent: Custom Datasheet Generation
        if (str_contains($text, 'datasheet') || str_contains($text, 'spec sheet') || str_contains($text, 'custom spec')) {
            $product = null;
            preg_match('/\b\d+\b/', $text, $matches);
            if (! empty($matches[0])) {
                $product = Product::find((int) $matches[0]);
            }
            if (! $product) {
                // Try matching product name in text
                $product = Product::where('status', 'publish')
                    ->where(function ($q) use ($text) {
                        $q->whereRaw('? LIKE CONCAT("%", LOWER(product_name), "%")', [$text])
                            ->orWhereRaw('? LIKE CONCAT("%", LOWER(product_code), "%")', [$text]);
                    })->first();
            }
            if (! $product) {
                $product = Product::where('status', 'publish')->whereNotNull('datasheet_file')->first()
                    ?: Product::where('status', 'publish')->first();
            }

            $prodId = $product ? (string) ($product->airtable_id ?: $product->id) : '1';
            $prodCode = $product ? (string) $product->product_code : 'AZ-SAMPLE-01';

            return [
                'content' => null,
                'tool_calls' => [
                    [
                        'id' => uniqid('mock_call_'),
                        'name' => 'generate_custom_datasheet',
                        'arguments' => [
                            'product_id' => $prodId,
                            'product_code' => $prodCode,
                        ],
                    ],
                ],
                'tokens_used' => 40,
            ];
        }

        // Intent: General FAQ Answers
        if (str_contains($text, 'hours') || str_contains($text, 'open') || str_contains($text, 'location') || str_contains($text, 'sydney') || str_contains($text, 'warranty')) {
            if (str_contains($text, 'warranty')) {
                return [
                    'content' => 'Azoogi offers up to 5-year commercial warranties on our architectural LED extrusions, drivers, and intelligent control hardware. All products are tested to AS/NZS electrical compliance standards.',
                    'tool_calls' => [],
                    'tokens_used' => 35,
                ];
            }

            return [
                'content' => 'Azoogi is headquartered in Sydney, NSW, Australia. Our sales engineering team operates Monday through Friday, 8:30 AM – 5:00 PM AEST. For project consultations, photometric schedules, or custom linear fabrication, please reach out via our contact page.',
                'tool_calls' => [],
                'tokens_used' => 45,
            ];
        }

        // Default & Search Intent: Extract parameters and search products
        $category = '';
        if (str_contains($text, 'downlight')) {
            $category = 'Downlight';
        } elseif (str_contains($text, 'strip') || str_contains($text, 'tape')) {
            $category = 'LED Strip';
        } elseif (str_contains($text, 'garden') || str_contains($text, 'spike')) {
            $category = 'Garden Light';
        } elseif (str_contains($text, 'pool') || str_contains($text, 'underwater')) {
            $category = 'Pool Light';
        } elseif (str_contains($text, 'profile') || str_contains($text, 'extrusion') || str_contains($text, 'linear')) {
            $category = 'Profile';
        } elseif (str_contains($text, 'driver') || str_contains($text, 'power')) {
            $category = 'Driver';
        }

        $ipRating = '';
        if (preg_match('/\b(IP[2456][0-9])\b/i', $text, $ipMatch)) {
            $ipRating = strtoupper($ipMatch[1]);
        }

        $dimming = '';
        if (preg_match('/\b(DALI(?:-2)?|Casambi|Triac|0-10V|1-10V|MADRIX|Silvair|Phase)\b/i', $text, $dimMatch)) {
            $dimming = $dimMatch[1];
        }

        return [
            'content' => null,
            'tool_calls' => [
                [
                    'id' => uniqid('mock_call_'),
                    'name' => 'public_search_and_filter_products',
                    'arguments' => [
                        'query' => $text,
                        'category' => $category,
                        'ip_rating' => $ipRating,
                        'dimming' => $dimming,
                    ],
                ],
            ],
            'tokens_used' => 45,
        ];
    }
}
