<?php

namespace App\Http\Resources;

use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Payment detail payload.
 *
 * @mixin Payment
 */
class PaymentResource extends JsonResource
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
            'type_label' => $payment->typeLabel(),
            'gateway' => $payment->gateway,
            'gateway_label' => $payment->gatewayLabel(),
            'source_type' => $payment->resolvedSourceType(),
            'source_type_label' => $payment->sourceTypeLabel(),
            'card' => $payment->cardDisplay(),
            'payer' => $payment->user?->name ?? $payment->client?->name ?? $payment->resolvedSourceName(),
            'email' => $payment->user?->email ?? $payment->client?->email,
            'phone' => $payment->client?->phone,
            'description' => $payment->resolvedDescription(),
            'payment_id' => $payment->payment_id,
            'gateway_id' => $payment->resolvedGatewayId(),
            'ip' => $payment->resolvedIp(),
            'order_uuid' => $payment->order?->uuid,
            'order_number' => $payment->order?->number,
            'date' => $payment->created_at?->translatedFormat('d M Y'),
            'time' => $payment->created_at?->translatedFormat('h:i A'),
            'created' => $payment->created_at?->translatedFormat('d M Y'),
        ];
    }
}
