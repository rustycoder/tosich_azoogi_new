<?php

declare(strict_types=1);

namespace App\Mcp\Tools\PublicChat;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Mcp\Contracts\McpToolInterface;
use App\Models\Enquiry;

class SubmitQuoteEnquiryTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'public_submit_quote_enquiry';
    }

    public function getDescription(): string
    {
        return 'Converts the current visitor quote list and customer contact details into a submitted quote enquiry in MySQL.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['name', 'email'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Customer or contact full name.',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Customer email address.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Customer phone number (optional).',
                ],
                'company' => [
                    'type' => 'string',
                    'description' => 'Company or organization name (optional).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Project notes, delivery requirements, or questions.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $name = trim((string) ($arguments['name'] ?? ''));
        $email = trim((string) ($arguments['email'] ?? ''));
        $phone = trim((string) ($arguments['phone'] ?? ''));
        $company = trim((string) ($arguments['company'] ?? ''));
        $notes = trim((string) ($arguments['notes'] ?? ''));

        if ($name === '' || $email === '') {
            return [
                'isError' => true,
                'content' => 'Name and Email are required to submit an enquiry.',
            ];
        }

        $quoteItems = session()->get('visitor_quote_list', []);

        if (empty($quoteItems) && empty($notes)) {
            return [
                'isError' => true,
                'content' => 'Quote list is currently empty. Please add products or specify project notes before submitting.',
            ];
        }

        try {
            $enquiry = Enquiry::create([
                'type' => EnquiryType::Quote,
                'status' => EnquiryStatus::Pending,
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'company' => $company ?: null,
                'message' => $notes ?: 'Quote request submitted via website AI assistant.',
                'payload' => [
                    'source' => 'ai_chat_widget',
                    'items' => array_values($quoteItems),
                    'submitted_at' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            // Clear session cart
            session()->forget('visitor_quote_list');

            return [
                'isError' => false,
                'content' => json_encode([
                    'enquiry_id' => $enquiry->id,
                    'status' => 'success',
                    'message' => "Thank you {$name}! Your quote request #{$enquiry->id} has been submitted successfully. Our engineering team will review it and follow up via {$email}.",
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        } catch (\Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Failed to submit quote enquiry: '.$e->getMessage(),
            ];
        }
    }
}
