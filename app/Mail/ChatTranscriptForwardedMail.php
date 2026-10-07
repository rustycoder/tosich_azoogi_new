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

class ChatTranscriptForwardedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, ChatMessage>|array<int, ChatMessage>  $messages
     * @param  array<int, mixed>  $quoteItems
     */
    public function __construct(
        public ChatSession $session,
        public Collection|array $messages,
        public ?string $senderName = null,
        public ?string $senderEmail = null,
        public ?string $notes = null,
        public array $quoteItems = [],
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = [];
        $email = $this->senderEmail ?: $this->session->lead_email;
        $name = $this->senderName ?: $this->session->lead_name;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $replyTo[] = new Address($email, $name ?: $email);
        }

        $projectRef = $this->session->project_name ? " - {$this->session->project_name}" : ($name ? " - {$name}" : " #{$this->session->id}");

        return new Envelope(
            subject: "[AI Chat Transcript] Architectural Lighting Consultation{$projectRef}",
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chat-transcript-forwarded',
        );
    }
}
