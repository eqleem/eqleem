<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Invoice detail payload.
 *
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Invoice $invoice */
        $invoice = $this->resource;
        $status = (string) ($invoice->initial_status ?? '');
        $vat = max(0, (int) $invoice->total_after_vat - (int) $invoice->total_before_vat);
        $due = max(0, (int) $invoice->total_after_vat - (int) $invoice->amount_paid);
        $order = $invoice->invoicable instanceof Order ? $invoice->invoicable : null;

        return [
            'id' => $invoice->id,
            'uuid' => $invoice->uuid,
            's_number' => $invoice->s_number,
            'status' => $status,
            'status_label' => match ($status) {
                'paid' => 'مدفوعة',
                'issued' => 'صادرة',
                'draft' => 'مسودة',
                'cancelled' => 'ملغاة',
                'partial' => 'مدفوعة جزئياً',
                default => $status !== '' ? $status : '-',
            },
            'status_color' => match ($status) {
                'paid' => 'green',
                'issued', 'partial' => 'yellow',
                'cancelled' => 'red',
                default => 'gray',
            },
            'type' => $invoice->type,
            'type_label' => $invoice->typeLabel(),
            'currency' => $invoice->currency,
            'note' => $invoice->note,
            'user' => $invoice->user?->name,
            'order_label' => $order ? 'طلب #'.($order->number ?? $order->id) : null,
            'order_uuid' => $order?->uuid,
            'total_before_vat' => (int) $invoice->total_before_vat,
            'total_before_vat_formatted' => Money::formatWithCurrency($invoice->total_before_vat, $invoice->currency),
            'vat' => $vat,
            'vat_formatted' => Money::formatWithCurrency($vat, $invoice->currency),
            'total_after_vat' => (int) $invoice->total_after_vat,
            'total_after_vat_formatted' => Money::formatWithCurrency($invoice->total_after_vat, $invoice->currency),
            'subtotal_after_vat' => (int) $invoice->subtotal_after_vat,
            'subtotal_after_vat_formatted' => Money::formatWithCurrency($invoice->subtotal_after_vat, $invoice->currency),
            'amount_paid' => (int) $invoice->amount_paid,
            'amount_paid_formatted' => Money::formatWithCurrency($invoice->amount_paid, $invoice->currency),
            'due' => $due,
            'due_formatted' => Money::formatWithCurrency($due, $invoice->currency),
            'issued' => $invoice->issued_on?->translatedFormat('d M Y'),
            'time' => $invoice->issued_on?->translatedFormat('h:i A') ?? $invoice->created_at?->translatedFormat('h:i A'),
            'items' => $invoice->relationLoaded('items')
                ? $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'quantity' => (int) $item->quantity,
                    'amount_after_vat' => (int) $item->amount_after_vat,
                    'amount_after_vat_formatted' => Money::formatWithCurrency($item->amount_after_vat, $invoice->currency),
                    'total_after_vat' => (int) $item->total_after_vat,
                    'total_after_vat_formatted' => Money::formatWithCurrency($item->total_after_vat, $invoice->currency),
                    'note' => $item->note,
                ])->values()->all()
                : [],
            'payments' => $invoice->relationLoaded('payments')
                ? $invoice->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'uuid' => $payment->uuid,
                    'amount' => (int) $payment->amount,
                    'amount_formatted' => Money::formatWithCurrency($payment->amount, $payment->currency),
                    'status' => $payment->resolvedStatus(),
                    'status_label' => $payment->statusLabel(),
                    'status_color' => $payment->statusBadgeColor(),
                    'created' => $payment->created_at?->translatedFormat('d M Y h:i A'),
                ])->values()->all()
                : [],
        ];
    }
}
