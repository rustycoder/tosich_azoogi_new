<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatIssueReportedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, ChatMessage>|array<int, ChatMessage>  $messages
     */
    public function __construct(
        public string $issueDescription,
        public ?ChatSession $session = null,
        public Collection|array $messages = [],
        public ?string $reporterName = null,
        public ?string $reporterEmail = null,
        public ?string $pageUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = [];
        $email = $this->reporterEmail ?: $this->session?->lead_email;
        $name = $this->reporterName ?: $this->session?->lead_name;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $replyTo[] = new Address($email, $name ?: $email);
        }

        $sessionTag = $this->session ? " (Session #{$this->session->id})" : '';

        return new Envelope(
            subject: "[AI Chat Issue Report] Feedback Submitted{$sessionTag}",
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chat-issue-reported',
        );
    }
}
