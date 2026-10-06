<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Enums\EnquiryType;
use App\Models\ChatSession;
use App\Models\Product;
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
        return 'public_submit_lead_enquiry';
    }

    public function getDescription(): string
    {
        return 'Submits verified visitor enquiries to the Sydney lighting engineering & sales team in MySQL. Supports 3 distinct enquiry types: "contact" (general inquiry/message/consultation), "quote" (quote request for items in quote list), and "product" (specific single product configuration enquiry). Only call this when required information (name, email, message) is collected.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => ['enquiry_type', 'name', 'email'],
            'properties' => [
                'enquiry_type' => [
                    'type' => 'string',
                    'enum' => ['contact', 'quote', 'product'],
                    'description' => 'The type of enquiry: "contact" for general messages, "quote" for quote list / project pricing, or "product" for single product specification inquiries.',
                ],
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
                    'description' => 'Company / architectural firm name (optional).',
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'The message, question, or project description from the visitor.',
                ],
                'product_id' => [
                    'type' => 'string',
                    'description' => 'Product name, SKU, or ID for "product" enquiries.',
                ],
                'product_specification' => [
                    'type' => 'string',
                    'description' => 'Configured specifications (e.g. 3000K, Black, DALI-2, 2.4m length) for "product" enquiries.',
                ],
                'project_name' => [
                    'type' => 'string',
                    'description' => 'Project title or location (optional).',
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
        $enquiryTypeRaw = strtolower(trim((string) ($arguments['enquiry_type'] ?? 'contact')));
        $name = trim((string) ($arguments['name'] ?? ''));
        $email = trim((string) ($arguments['email'] ?? ''));
        $phone = trim((string) ($arguments['phone'] ?? ''));
        $company = trim((string) ($arguments['company'] ?? ''));
        $message = trim((string) ($arguments['message'] ?? ''));
        $productId = trim((string) ($arguments['product_id'] ?? ''));
        $productSpec = trim((string) ($arguments['product_specification'] ?? ''));
        $projectName = trim((string) ($arguments['project_name'] ?? ''));
        $sessionUuid = trim((string) ($arguments['session_uuid'] ?? ''));

        if ($name === '' || $email === '') {
            return [
                'result' => [
                    'status' => 'validation_error',
                    'error' => 'Full name and email address are required to submit an enquiry. Please ask the visitor for these details.',
                ],
            ];
        }

        $type = match ($enquiryTypeRaw) {
            'quote' => EnquiryType::Quote,
            'product' => EnquiryType::Product,
            default => EnquiryType::Contact,
        };

        try {
            if ($type === EnquiryType::Contact) {
                if ($message === '') {
                    $message = 'General inquiry submitted via website AI assistant.';
                }

                $enquiry = $this->enquiryService->submit(EnquiryType::Contact, [
                    'name' => $name,
                    'email' => $email,
                    'company' => $company ?: null,
                    'message' => $message,
                    'payload' => [
                        'source' => 'ai_chat_assistant',
                        'phone' => $phone ?: null,
                        'submitted_at' => now()->toIso8601String(),
                    ],
                ]);

                $confirmationMessage = "Thank you {$name}! Your contact message (Ref #{$enquiry->id}) has been forwarded to our Sydney engineering team. We will be in touch via {$email} shortly.";
            } elseif ($type === EnquiryType::Product) {
                // Single Product Enquiry
                $productName = $productId;
                if (! empty($productId)) {
                    $foundProduct = Product::query()
                        ->where(function ($q) use ($productId) {
                            if (is_numeric($productId)) {
                                $q->where('id', (int) $productId);
                            }
                            $q->orWhere('airtable_id', $productId)
                                ->orWhere('product_code', $productId)
                                ->orWhere('slug', $productId)
                                ->orWhere('product_name', 'like', "%{$productId}%");
                        })
                        ->first();

                    if ($foundProduct) {
                        $productName = $foundProduct->product_name.' ('.$foundProduct->product_code.')';
                    }
                }

                $enquiry = $this->enquiryService->submit(EnquiryType::Product, [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'company' => $company ?: null,
                    'message' => $message ?: "Product enquiry for {$productName}",
                    'payload' => [
                        'source' => 'ai_chat_assistant',
                        'product' => $productName,
                        'specification' => $productSpec ?: 'Standard specifications',
                        'project' => $projectName ?: null,
                        'submitted_at' => now()->toIso8601String(),
                    ],
                ]);

                $confirmationMessage = "Thank you {$name}! Your product specification enquiry #{$enquiry->id} for '{$productName}' has been submitted. A lighting specialist will review your requirements and reply to {$email}.";
            } else {
                // Quote Request Enquiry
                $quoteItems = session()->get('visitor_quote_list', []);

                $enquiry = $this->enquiryService->submit(EnquiryType::Quote, [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'company' => $company ?: null,
                    'message' => $message ?: 'Quote request submitted via website AI assistant.',
                    'payload' => [
                        'source' => 'ai_chat_assistant',
                        'items' => array_values($quoteItems),
                        'project' => $projectName ?: null,
                        'submitted_at' => now()->toIso8601String(),
                    ],
                ]);

                // Clear session quote cart upon successful quote request submission
                session()->forget('visitor_quote_list');

                $itemsCount = count($quoteItems);
                $confirmationMessage = "Thank you {$name}! Your quote request #{$enquiry->id} ({$itemsCount} item".($itemsCount === 1 ? '' : 's').') has been submitted. Our sales engineering team will prepare your quote and contact you via '.$email.'.';
            }

            // Sync with ChatSession record
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

            return [
                'result' => [
                    'status' => 'success',
                    'enquiry_type' => $type->value,
                    'enquiry_id' => $enquiry->id,
                    'message' => $confirmationMessage,
                ],
                'cards' => [
                    'type' => 'lead_confirmation_card',
                    'enquiry_type' => $type->value,
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
