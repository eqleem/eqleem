<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, phone?: string, address?: string, subject: string, message: string}  $contact
     */
    public function __construct(public array $contact) {}

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

        return new Envelope(
            replyTo: $replyTo,
            subject: '[اتصل بنا] '.$this->contact['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact-message',
        );
    }
}
