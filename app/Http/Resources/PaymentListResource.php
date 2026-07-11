<?php

namespace App\Http\Resources;

use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payment row for dashboard tables.
 *
 * @mixin Payment
 */
class PaymentListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Payment $payment */
        $payment = $this->resource;
        $status = (string) ($payment->resolvedStatus() ?? '');

        return [
            'id' => $payment->id,
            'uuid' => $payment->uuid,
            'amount' => (int) $payment->amount,
            'amount_formatted' => Money::formatWithCurrency($payment->amount, $payment->currency),
            'currency' => $payment->currency,
            'status' => $status,
            'status_label' => $payment->statusLabel(),
            'status_color' => $payment->statusBadgeColor(),
            'reason' => $payment->reason,
            'reason_label' => $payment->reasonLabel(),
            'gateway' => $payment->gateway,
            'gateway_label' => $payment->gatewayLabel(),
            'source_type' => $payment->resolvedSourceType(),
            'source_type_label' => $payment->sourceTypeLabel(),
            'card' => $payment->cardDisplay(),
            'payer' => $payment->relationLoaded('user') || $payment->relationLoaded('client')
                ? ($payment->user?->name ?? $payment->client?->name ?? $payment->resolvedSourceName())
                : $payment->resolvedSourceName(),
            'email' => $payment->user?->email ?? $payment->client?->email,
            'order_uuid' => $payment->order?->uuid,
            'order_number' => $payment->order?->number,
            'date' => $payment->created_at?->translatedFormat('d M Y'),
            'time' => $payment->created_at?->translatedFormat('h:i A'),
        ];
    }
}
