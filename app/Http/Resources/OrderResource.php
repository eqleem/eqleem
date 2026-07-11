<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Full order detail for the dashboard.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
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
        $client = $order->client;

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
            'channel' => $order->channel,
            'channel_label' => $order->channelLabel(),
            'payment_method_label' => $order->paymentMethodLabel(),
            'shipping_method_label' => $order->shippingMethodLabel(),
            'shipping_address' => $order->shippingAddressLabel(),
            'tracking_number' => data_get($order->meta, 'tracking_number'),
            'notes' => $order->notes,
            'currency_code' => $order->currency_code,
            'subtotal' => (int) $order->subtotal,
            'subtotal_formatted' => Money::formatWithCurrency($order->subtotal, $order->currency_code),
            'discount_total' => (int) $order->discount_total,
            'discount_total_formatted' => Money::formatWithCurrency($order->discount_total, $order->currency_code),
            'tax_total' => (int) $order->tax_total,
            'tax_total_formatted' => Money::formatWithCurrency($order->tax_total, $order->currency_code),
            'shipping_fee' => $order->shippingFee(),
            'shipping_fee_formatted' => Money::formatWithCurrency($order->shippingFee(), $order->currency_code),
            'grand_total' => (int) $order->grand_total,
            'grand_total_formatted' => Money::formatWithCurrency($order->grand_total, $order->currency_code),
            'paid_total' => (int) $order->paid_total,
            'paid_total_formatted' => Money::formatWithCurrency($order->paid_total, $order->currency_code),
            'due_total' => (int) $order->due_total,
            'due_total_formatted' => Money::formatWithCurrency($order->due_total, $order->currency_code),
            'created' => $issuedAt?->locale(app()->getLocale())->diffForHumans(),
            'issued_at' => $issuedAt?->toIso8601String(),
            'client' => $client ? [
                'id' => $client->id,
                'uuid' => $client->uuid,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'avatar' => $this->clientAvatar($client->id, data_get($client->meta, 'avatar')),
            ] : null,
            'items' => $this->itemsPayload($order),
            'payments' => $order->relationLoaded('payments')
                ? $order->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'uuid' => $payment->uuid,
                    'method' => $payment->sourceTypeLabel(),
                    'status' => $payment->resolvedStatus(),
                    'status_label' => $payment->statusLabel(),
                    'status_color' => $payment->statusBadgeColor(),
                    'amount' => (int) $payment->amount,
                    'amount_formatted' => Money::formatWithCurrency($payment->amount, $payment->currency),
                    'currency' => $payment->currency,
                    'created' => $payment->created_at?->translatedFormat('d M Y h:i A'),
                ])->values()->all()
                : [],
            'activity' => $this->activityPayload($order),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function itemsPayload(Order $order): array
    {
        $rows = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get(['id', 'name', 'qty', 'unit_price', 'discount_total', 'line_total', 'sku', 'meta']);

        return $rows->map(function (object $item): array {
            $meta = is_string($item->meta ?? null)
                ? (json_decode($item->meta, true) ?: [])
                : (array) ($item->meta ?? []);
            $type = (string) ($meta['type'] ?? 'other');

            return [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'type' => $type,
                'type_label' => Order::itemTypeOptions()[$type] ?? $type,
                'type_color' => match ($type) {
                    'product', 'digital_service' => 'blue',
                    'digital_product', 'course' => 'purple',
                    'menu' => 'yellow',
                    'service' => 'green',
                    default => 'gray',
                },
                'qty' => (int) $item->qty,
                'unit_price' => (int) $item->unit_price,
                'unit_price_formatted' => Money::formatWithCurrency($item->unit_price),
                'discount' => (int) $item->discount_total,
                'discount_formatted' => Money::formatWithCurrency($item->discount_total),
                'line_total' => (int) $item->line_total,
                'line_total_formatted' => Money::formatWithCurrency($item->line_total),
                'description' => filled($meta['description'] ?? null) ? (string) $meta['description'] : null,
                'image_url' => $meta['image_url'] ?? null,
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function activityPayload(Order $order): array
    {
        $statusEntries = $order->statuses()
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn ($status): array => [
                'key' => 'status-'.$status->id,
                'type' => 'status',
                'title' => 'تغيير حالة الطلب',
                'status' => $status->name,
                'status_label' => Order::statusLabelFor((string) $status->name),
                'status_color' => Order::statusBadgeColorFor((string) $status->name),
                'date' => $status->created_at?->translatedFormat('d M Y h:i A'),
                'sort' => $status->created_at?->getTimestamp() ?? 0,
            ]);

        $activityEntries = $order->activitiesAsSubject()
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn ($activity): array => [
                'key' => 'activity-'.$activity->id,
                'type' => 'activity',
                'title' => filled($activity->description) ? (string) $activity->description : 'نشاط على الطلب',
                'status' => null,
                'status_label' => null,
                'status_color' => null,
                'date' => $activity->created_at?->translatedFormat('d M Y h:i A'),
                'sort' => $activity->created_at?->getTimestamp() ?? 0,
            ]);

        return $statusEntries
            ->concat($activityEntries)
            ->sortByDesc('sort')
            ->values()
            ->map(fn (array $entry): array => collect($entry)->except('sort')->all())
            ->all();
    }

    private function clientAvatar(int $id, mixed $metaAvatar): string
    {
        if (filled($metaAvatar)) {
            if (str_starts_with((string) $metaAvatar, 'http')) {
                return (string) $metaAvatar;
            }

            return Storage::disk('public')->url((string) $metaAvatar);
        }

        return 'https://api.dicebear.com/9.x/fun-emoji/svg?seed='.$id;
    }
}
