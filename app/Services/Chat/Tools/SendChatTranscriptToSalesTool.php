<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Mail\ChatTranscriptForwardedMail;
use App\Models\ChatSession;
use App\Services\Chat\Contracts\IChatTool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendChatTranscriptToSalesTool implements IChatTool
{
    public function getName(): string
    {
        return 'public_send_chat_transcript_to_sales';
    }

    public function getDescription(): string
    {
        return 'Forwards the complete conversation transcript, project specifications, client contact details, and shortlisted fixtures to the Azoogi Sydney sales & engineering team (sales@azoogi.com.au) for formal review, photometric verification, stock scheduling, or pricing.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => [],
            'properties' => [
                'session_uuid' => [
                    'type' => 'string',
                    'description' => 'Chat session UUID.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Client / Visitor full name (optional if already provided).',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Client work or contact email address (optional if already provided).',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Client contact phone number (optional).',
                ],
                'company' => [
                    'type' => 'string',
                    'description' => 'Architectural practice, electrical contracting firm, or business name (optional).',
                ],
                'project_name' => [
                    'type' => 'string',
                    'description' => 'Project title or location reference (optional).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Any additional notes, specific fixture questions, or instructions for the sales engineering team.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $sessionUuid = trim((string) ($arguments['session_uuid'] ?? ''));
        $name = trim((string) ($arguments['name'] ?? ''));
        $email = trim((string) ($arguments['email'] ?? ''));
        $phone = trim((string) ($arguments['phone'] ?? ''));
        $company = trim((string) ($arguments['company'] ?? ''));
        $projectName = trim((string) ($arguments['project_name'] ?? ''));
        $notes = trim((string) ($arguments['notes'] ?? ''));

        $session = null;
        if (! empty($sessionUuid)) {
            $session = ChatSession::where('uuid', $sessionUuid)->first();
        }

        if (! $session) {
            $session = ChatSession::latest('id')->first();
        }

        if (! $session) {
            return [
                'result' => [
                    'status' => 'error',
                    'message' => 'No active chat session found to forward transcript.',
                ],
            ];
        }

        // Update session lead fields if new data provided
        $updates = [];
        if ($name !== '' && empty($session->lead_name)) {
            $updates['lead_name'] = $name;
        }
        if ($email !== '' && empty($session->lead_email)) {
            $updates['lead_email'] = $email;
        }
        if ($phone !== '' && empty($session->lead_phone)) {
            $updates['lead_phone'] = $phone;
        }
        if ($company !== '' && empty($session->lead_company)) {
            $updates['lead_company'] = $company;
        }
        if ($projectName !== '' && empty($session->project_name)) {
            $updates['project_name'] = $projectName;
        }

        if (! empty($updates)) {
            $session->update($updates);
        }

        $messages = $session->messages()->orderBy('id', 'asc')->get();
        $quoteItems = session()->get('visitor_quote_list', []);
        $recipient = (string) config('mail.sales_address', env('SALES_EMAIL', 'sales@azoogi.com.au'));

        try {
            Mail::to($recipient)->send(new ChatTranscriptForwardedMail(
                session: $session,
                messages: $messages,
                senderName: $name ?: $session->lead_name,
                senderEmail: $email ?: $session->lead_email,
                notes: $notes ?: null,
                quoteItems: is_array($quoteItems) ? array_values($quoteItems) : [],
            ));

            $leadContact = $session->lead_email ?: ($session->lead_name ?: 'your contact address');

            return [
                'result' => [
                    'status' => 'success',
                    'session_uuid' => $session->uuid,
                    'recipient' => $recipient,
                    'message' => "The complete conversation transcript and fixture requirements have been securely forwarded to our Sydney sales engineering desk ({$recipient}). Our team will follow up via {$leadContact}.",
                ],
                'cards' => [
                    'type' => 'transcript_sent_card',
                    'session_uuid' => $session->uuid,
                    'recipient' => $recipient,
                    'lead_name' => $session->lead_name,
                    'lead_email' => $session->lead_email,
                ],
            ];
        } catch (Throwable $e) {
            Log::error('Failed to forward chat transcript: '.$e->getMessage(), ['exception' => $e]);

            return [
                'result' => [
                    'status' => 'error',
                    'message' => 'Unable to forward transcript at this moment. Please email sales@azoogi.com.au directly.',
                ],
            ];
        }
    }
}
