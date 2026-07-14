<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name?: string, email?: string, phone?: string, address?: string, subject?: string, message?: string}  $contact
     */
    public function __construct(
        public array $contact,
        public ?Tenant $tenant = null,
        public ?string $managePageUrl = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = [];

        if (filled($this->contact['email'] ?? null)) {
            $replyTo[] = new Address(
                $this->contact['email'],
                filled($this->contact['name'] ?? null) ? $this->contact['name'] : null,
            );
        }

        $subject = (string) ($this->contact['subject'] ?? 'رسالة من نموذج اتصل بنا');
        $tenantName = filled($this->tenant?->name) ? ' — '.$this->tenant->name : '';

        return new Envelope(
            replyTo: $replyTo,
            subject: 'رسالة جديدة'.$tenantName.' — '.$subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'mail.contact-message',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
