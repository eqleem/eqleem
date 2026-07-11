<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard orders table.
 *
 * @mixin Order
 */
class OrderListResource extends JsonResource
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
            'payment_status_label' => $this->paymentStatusBadgeLabel($paymentStatus),
            'payment_status_color' => $this->paymentStatusBadgeColor($paymentStatus),
            'grand_total' => (int) $order->grand_total,
            'grand_total_formatted' => Money::formatWithCurrency($order->grand_total, $order->currency_code),
            'currency_code' => $order->currency_code,
            'client' => $order->client?->name,
            'created' => $issuedAt?->locale(app()->getLocale())->diffForHumans(),
            'issued_at' => $issuedAt?->toIso8601String(),
        ];
    }

    private function paymentStatusBadgeLabel(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'حالة الدفع: مدفوع',
            'unpaid' => 'حالة الدفع: لم تتم',
            'partial' => 'حالة الدفع: مدفوع جزئياً',
            'refunded' => 'حالة الدفع: مسترجع',
            default => 'حالة الدفع: '.$this->resource->paymentStatusLabel(),
        };
    }

    private function paymentStatusBadgeColor(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'gray',
            'unpaid', 'partial' => 'yellow',
            default => 'gray',
        };
    }
}
