<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for invoice list tables.
 *
 * @mixin Invoice
 */
class InvoiceListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Invoice $invoice */
        $invoice = $this->resource;
        $status = (string) ($invoice->initial_status ?? '');
        $issuedOn = $invoice->issued_on;
        $createdAt = $invoice->created_at;

        return [
            'id' => $invoice->id,
            'uuid' => $invoice->uuid,
            's_number' => $invoice->s_number,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_color' => $this->statusColor($status),
            'type' => $invoice->type,
            'type_label' => $invoice->typeLabel(),
            'order_label' => $this->orderLabel($invoice),
            'order_uuid' => $invoice->invoicable instanceof Order ? $invoice->invoicable->uuid : null,
            'total_after_vat' => (int) $invoice->total_after_vat,
            'total_after_vat_formatted' => Money::formatWithCurrency($invoice->total_after_vat, $invoice->currency),
            'amount_paid' => (int) $invoice->amount_paid,
            'amount_paid_formatted' => Money::formatWithCurrency($invoice->amount_paid, $invoice->currency),
            'currency' => $invoice->currency,
            'issued' => $issuedOn?->translatedFormat('d M Y'),
            'date' => $createdAt?->translatedFormat('d M Y'),
            'time' => $createdAt?->translatedFormat('h:i A'),
        ];
    }

    private function orderLabel(Invoice $invoice): ?string
    {
        if ($invoice->invoicable_type !== Order::class || ! $invoice->invoicable instanceof Order) {
            return null;
        }

        return 'طلب #'.($invoice->invoicable->number ?? $invoice->invoicable->id);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'مدفوعة',
            'issued' => 'صادرة',
            'draft' => 'مسودة',
            'cancelled' => 'ملغاة',
            'partial' => 'مدفوعة جزئياً',
            default => $status !== '' ? $status : '-',
        };
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'paid' => 'green',
            'issued', 'partial' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
