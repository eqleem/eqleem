<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Tenant $tenant,
        public string $pageUrl,
        public string $dashboardUrl,
        public string $managePageUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مرحباً بك في إقليم — صفحتك جاهزة للانطلاق 🚀',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.welcome-user',
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
