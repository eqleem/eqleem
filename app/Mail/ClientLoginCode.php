<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientLoginCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public Tenant $tenant,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رابط وكود تسجيل الدخول - '.$this->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.client-login-code',
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
