<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, object>  $items
     */
    public function __construct(
        public Order $order,
        public Tenant $tenant,
        public string $customerName,
        public Collection $items,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تأكيد طلبك رقم '.$this->order->number.' — '.$this->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.order-confirmation',
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
