<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Enums\EnquiryType;
use App\Models\ChatSession;
use App\Services\Chat\Contracts\IChatTool;
use App\Services\Contracts\IEnquiryService;
use Throwable;

class SubmitLeadEnquiryTool implements IChatTool
{
    public function __construct(
        protected IEnquiryService $enquiryService
    ) {}

    public function getName(): string
    {
        return 'public_submit_quote_enquiry';
    }

    public function getDescription(): string
    {
        return 'Submits the visitor’s quote list, project requirements, and contact details (name, email, phone, company) as an official enquiry lead in MySQL, notifying lighting engineers.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => ['name', 'email'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Customer / contact full name.',
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
                    'description' => 'Company / architectural firm name (optional).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Project notes, delivery requirements, or technical questions.',
                ],
                'session_uuid' => [
                    'type' => 'string',
                    'description' => 'Active chat session UUID.',
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
        $sessionUuid = trim((string) ($arguments['session_uuid'] ?? ''));

        if ($name === '' || $email === '') {
            return [
                'result' => ['error' => 'Name and Email are required to submit an enquiry.'],
            ];
        }

        $quoteItems = session()->get('visitor_quote_list', []);

        try {
            $enquiry = $this->enquiryService->submit(EnquiryType::Quote, [
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'company' => $company ?: null,
                'message' => $notes ?: 'Quote inquiry submitted via website AI assistant.',
                'payload' => [
                    'source' => 'ai_chat_assistant',
                    'items' => array_values($quoteItems),
                    'submitted_at' => now()->toIso8601String(),
                ],
            ]);

            // Sync with ChatSession if provided
            if (! empty($sessionUuid)) {
                $session = ChatSession::where('uuid', $sessionUuid)->first();
                if ($session) {
                    $session->update([
                        'lead_name' => $name,
                        'lead_email' => $email,
                        'lead_phone' => $phone ?: null,
                        'lead_company' => $company ?: null,
                        'enquiry_id' => $enquiry->id,
                        'status' => 'completed',
                    ]);
                }
            }

            // Clear session cart
            session()->forget('visitor_quote_list');

            return [
                'result' => [
                    'status' => 'success',
                    'enquiry_id' => $enquiry->id,
                    'message' => "Thank you {$name}! Your quote request #{$enquiry->id} has been submitted. Our lighting engineering team will review your specifications and contact you via {$email}.",
                ],
                'cards' => [
                    'type' => 'lead_confirmation_card',
                    'enquiry_id' => $enquiry->id,
                    'name' => $name,
                    'email' => $email,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'result' => [
                    'status' => 'error',
                    'message' => 'Failed to submit enquiry: '.$e->getMessage(),
                ],
            ];
        }
    }
}
