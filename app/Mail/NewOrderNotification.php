<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NewOrderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, object>  $items
     */
    public function __construct(
        public Order $order,
        public Tenant $tenant,
        public User $owner,
        public string $customerName,
        public string $customerPhone,
        public ?string $customerEmail,
        public Collection $items,
        public string $orderDetailUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'طلب جديد رقم '.$this->order->number.' — '.$this->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.new-order-notification',
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
