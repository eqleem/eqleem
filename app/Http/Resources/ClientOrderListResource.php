<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for a client's orders tab.
 *
 * @mixin Order
 */
class ClientOrderListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;
        $issuedAt = $order->issued_at ?? $order->created_at;
        $status = $order->statusValue();
        $paymentStatus = (string) $order->payment_status;

        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'number' => $order->number ?? (string) $order->id,
            'status' => $status,
            'status_label' => Order::statusLabelFor($status),
            'status_color' => Order::statusBadgeColorFor($status),
            'payment_status' => $paymentStatus,
            'payment_status_label' => $order->paymentStatusLabel(),
            'payment_status_color' => $order->paymentStatusBadgeColor(),
            'grand_total' => (int) $order->grand_total,
            'grand_total_formatted' => Money::formatWithCurrency($order->grand_total, $order->currency_code),
            'currency_code' => $order->currency_code,
            'items_count' => (int) ($order->items_count ?? 0),
            'date' => $issuedAt?->translatedFormat('d M Y'),
            'time' => $issuedAt?->translatedFormat('h:i A'),
            'issued_at' => $issuedAt?->toIso8601String(),
        ];
    }
}
