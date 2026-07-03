<?php

namespace App\Models;

use App\Support\Money;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStatus\HasStatuses;

class Payment extends Model
{
    use BelongsToTenant, HasStatuses, HasUuid, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'client_id',
        'order_id',
        'purchased_id',
        'purchased_type',
        'from_id',
        'from_type',
        'to_id',
        'to_type',
        'amount',
        'currency',
        'status',
        'meta',
        'notes',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $casts = [
        'meta' => 'json',
        'amount' => 'integer',
        'captured' => 'boolean',
        'refunded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function purchased()
    {
        return $this->morphTo();
    }

    public function paymentable()
    {
        return $this->morphTo();
    }

    public function payerName(): string
    {
        if ($this->user_id) {
            return $this->user->name;
        }

        if ($this->client_id) {
            return $this->client->name;
        }

        if ($name = $this->resolvedSourceName()) {
            return $name;
        }

        return '-';
    }

    public function metaValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta, $key, $default);
    }

    public function metaSource(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta, 'source.'.$key, $default);
    }

    public function resolvedStatus(): ?string
    {
        return $this->status() ?: $this->initial_status ?: $this->metaValue('status');
    }

    public function resolvedDescription(): ?string
    {
        return $this->description ?: $this->metaValue('description');
    }

    public function resolvedIp(): ?string
    {
        return $this->ip ?: $this->metaValue('ip');
    }

    public function resolvedInvoiceId(): int|string|null
    {
        return $this->invoice_id ?: $this->metaValue('invoice_id');
    }

    public function resolvedGatewayId(): ?string
    {
        return $this->gateway_id ?: $this->metaSource('gateway_id');
    }

    public function resolvedCallbackUrl(): ?string
    {
        return $this->metaValue('callback_url');
    }

    public function resolvedSourceType(): ?string
    {
        return $this->source_type ?: $this->metaSource('type');
    }

    public function resolvedSourceName(): ?string
    {
        return $this->source_name ?: $this->metaSource('name');
    }

    public function resolvedSourceCompany(): ?string
    {
        return $this->source_company ?: $this->metaSource('company');
    }

    public function resolvedSourceNumber(): ?string
    {
        return $this->source_number ?: $this->metaSource('number');
    }

    public function resolvedSourceMessage(): ?string
    {
        return $this->metaSource('message');
    }

    public function resolvedSourceReferenceNumber(): ?string
    {
        return $this->metaSource('reference_number');
    }

    public function resolvedSourceLastFour(): ?int
    {
        if ($this->source_last_four) {
            return (int) $this->source_last_four;
        }

        $number = $this->resolvedSourceNumber();

        if (! $number) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $number);

        if ($digits && strlen($digits) >= 4) {
            return (int) substr($digits, -4);
        }

        return null;
    }

    public function resolvedSourceExpiryMonth(): ?int
    {
        if ($this->source_expiry_month) {
            return (int) $this->source_expiry_month;
        }

        $month = $this->metaSource('month');

        return $month !== null ? (int) $month : null;
    }

    public function resolvedSourceExpiryYear(): ?int
    {
        if ($this->source_expiry_year) {
            return (int) $this->source_expiry_year;
        }

        $year = $this->metaSource('year');

        return $year !== null ? (int) $year : null;
    }

    public function resolvedCaptured(): bool
    {
        if ($this->captured) {
            return true;
        }

        return (bool) $this->metaValue('captured', false);
    }

    public function resolvedFee(): ?int
    {
        $fee = $this->metaValue('fee');

        return $fee !== null ? (int) $fee : null;
    }

    public function resolvedRefunded(): ?int
    {
        $refunded = $this->metaValue('refunded');

        return $refunded !== null ? (int) $refunded : null;
    }

    public function resolvedRefundedAt(): ?Carbon
    {
        if ($this->refunded_at) {
            return $this->refunded_at;
        }

        $refundedAt = $this->metaValue('refunded_at');

        return $refundedAt ? Carbon::parse($refundedAt) : null;
    }

    public function resolvedGatewayCreatedAt(): ?Carbon
    {
        $createdAt = $this->metaValue('created_at');

        return $createdAt ? Carbon::parse($createdAt) : null;
    }

    public function resolvedGatewayUpdatedAt(): ?Carbon
    {
        $updatedAt = $this->metaValue('updated_at');

        return $updatedAt ? Carbon::parse($updatedAt) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedMetadata(): array
    {
        $metadata = $this->metaValue('metadata', []);

        return is_array($metadata) ? $metadata : [];
    }

    public function metadataLabel(string $key): string
    {
        return match ($key) {
            'plan_id' => 'الباقة',
            'tenant_id' => 'المتجر',
            'order_id' => 'الطلب',
            default => $key,
        };
    }

    /**
     * @return list<array{label: string, value: string, dir: ?string, mono: bool}>
     */
    public function gatewayDetailRows(): array
    {
        $rows = [
            ['label' => 'رابط العودة', 'value' => $this->resolvedCallbackUrl(), 'dir' => 'ltr', 'mono' => true],
            ['label' => 'المبلغ (البوابة)', 'value' => $this->metaValue('amount_format'), 'dir' => 'ltr'],
            ['label' => 'الرسوم (البوابة)', 'value' => $this->metaValue('fee_format'), 'dir' => 'ltr'],
            ['label' => 'المسترجع (البوابة)', 'value' => $this->metaValue('refunded_format'), 'dir' => 'ltr'],
            ['label' => 'المحصّل (البوابة)', 'value' => $this->metaValue('captured_format'), 'dir' => 'ltr'],
        ];

        if ($createdAt = $this->resolvedGatewayCreatedAt()) {
            $rows[] = ['label' => 'تاريخ تسجيل البوابة', 'value' => $createdAt->translatedFormat('d M Y h:i A')];
        }

        if ($updatedAt = $this->resolvedGatewayUpdatedAt()) {
            $rows[] = ['label' => 'آخر تحديث (البوابة)', 'value' => $updatedAt->translatedFormat('d M Y h:i A')];
        }

        foreach ($this->resolvedMetadata() as $key => $value) {
            if (filled($value)) {
                $rows[] = [
                    'label' => $this->metadataLabel((string) $key),
                    'value' => (string) $value,
                    'dir' => is_numeric($value) ? 'ltr' : null,
                    'mono' => false,
                ];
            }
        }

        return array_values(array_filter($rows, fn (array $row): bool => filled($row['value'] ?? null)));
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'payment' => 'دفع',
            'refund' => 'استرجاع',
            default => $this->type ?: '-',
        };
    }

    public function capturedLabel(): string
    {
        return $this->resolvedCaptured() ? 'تم التحصيل' : 'لم يُحصّل';
    }

    public function statusLabel(): string
    {
        $status = $this->resolvedStatus();

        return match ($status) {
            'paid' => 'مدفوع',
            'failed' => 'فشل',
            'refunded' => 'مسترجع',
            'pending' => 'قيد الانتظار',
            'authorized' => 'مصرّح',
            'void' => 'ملغي',
            default => $status ?? '-',
        };
    }

    public function statusBadgeColor(): string
    {
        $status = $this->resolvedStatus();

        return match ($status) {
            'paid' => 'green',
            'failed', 'void' => 'red',
            'refunded' => 'purple',
            'pending' => 'yellow',
            default => 'gray',
        };
    }

    public function reasonLabel(): string
    {
        return match ($this->reason) {
            'tenant-subscribe-to-plan' => 'اشتراك بالباقة',
            'client-buy-from-tenant' => 'شراء من المتجر',
            'tenant-client-subscribe-to-tenant-plan' => 'اشتراك عميل',
            default => $this->resolvedDescription() ?: $this->reason ?: '-',
        };
    }

    public function gatewayLabel(): string
    {
        return match ($this->gateway) {
            'moyasar' => 'ميسر',
            default => $this->gateway ?: '-',
        };
    }

    public function sourceTypeLabel(): string
    {
        return match ($this->resolvedSourceType()) {
            'creditcard' => 'بطاقة ائتمان',
            'applepay' => 'Apple Pay',
            'stcpay' => 'STC Pay',
            default => $this->resolvedSourceType() ?: '-',
        };
    }

    public function cardDisplay(): ?string
    {
        $lastFour = $this->resolvedSourceLastFour();

        if (! $lastFour) {
            return null;
        }

        $company = $this->resolvedSourceCompany() ? strtoupper($this->resolvedSourceCompany()) : '••••';

        return "{$company} · {$lastFour}";
    }

    public function getStatusLabelAttribute()
    {
        return $this->statusLabel();
    }

    public function getWhoAttribute()
    {
        $sourceName = $this->resolvedSourceName();

        if ($this->user_id) {
            return $this->user->name.($sourceName ? ' ('.$sourceName.')' : '');
        }

        if ($this->client_id) {
            return $this->client->name.($sourceName ? ' ('.$sourceName.')' : '');
        }

        return '-';
    }

    public function formattedAmount(): string
    {
        return Money::format($this->amount);
    }
}
