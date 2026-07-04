<?php

namespace App\Models;

use App\Support\Money;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\ModelStatus\HasStatuses;

class Order extends Model
{
    use BelongsToTenant, HasStatuses, HasUuid, LogsActivity, SoftDeletes;

    public bool $logEmptyChanges = false;

    protected $fillable = [
        'tenant_id',
        'type',
        'status',
        'channel',
        'number',
        'number_sequence',
        'client_id',
        'currency_code',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'paid_total',
        'due_total',
        'payment_status',
        'issued_at',
        'created_by',
        'register_shift_id',
        'branch_id',
        'notes',
        'financial_status',
        'fulfillment_status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'meta' => 'array',
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'tax_total' => 'integer',
            'grand_total' => 'integer',
            'paid_total' => 'integer',
            'due_total' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function tapActivity($activity, $eventName)
    {
        $activity->tenant_id = $this->tenant_id;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }

    public static function walkingClientLabel(): string
    {
        return __('Walking client');
    }

    public static function minorFromDecimal(float|string|null $amount): int
    {
        return Money::toMinor($amount);
    }

    public static function fromMinor(int|string|null $amount): float
    {
        return Money::fromMinor($amount);
    }

    public static function formatMinor(int|string|null $amount): string
    {
        return Money::format($amount);
    }

    /**
     * @param  array<int, array{qty: int|string, unit_price: float|string, discount?: float|string}>  $items
     * @return array{subtotal: int, discount_total: int, tax_total: int, grand_total: int}
     */
    public static function calculateTotalsMinor(array $items): array
    {
        $subtotal = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $qty = max(0, (int) ($item['qty'] ?? 0));
            $unitPrice = self::minorFromDecimal($item['unit_price'] ?? 0);
            $discount = self::minorFromDecimal($item['discount'] ?? 0);
            $subtotal += $qty * $unitPrice;
            $discountTotal += $discount;
        }

        $taxTotal = 0;
        $grandTotal = max(0, $subtotal - $discountTotal + $taxTotal);

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'مسودة',
            'open' => 'مفتوح',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'unpaid' => 'غير مدفوع',
            'partial' => 'مدفوع جزئياً',
            'paid' => 'مدفوع',
        ];
    }

    public static function paymentMethodOptions(): array
    {
        return [
            'cash' => 'نقداً',
            'card' => 'بطاقة',
            'bank_transfer' => 'تحويل بنكي',
            'online' => 'دفع إلكتروني',
        ];
    }

    public static function itemTypeOptions(): array
    {
        return [
            'product' => 'منتج',
            'service' => 'خدمة',
            'course' => 'دورة',
            'other' => 'أخرى',
        ];
    }

    public function formatAmount(int|string|null $amount): string
    {
        return self::formatMinor($amount);
    }

    public function formattedGrandTotal(): string
    {
        return $this->formatAmount($this->grand_total).' '.$this->currency_code;
    }

    public function statusValue(): string
    {
        return (string) ($this->getAttributes()['status'] ?? '');
    }

    public static function statusLabelFor(string $status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'open' => 'مفتوح',
            'confirmed' => 'مؤكد',
            'partially_paid' => 'مدفوع جزئياً',
            'paid' => 'مدفوع',
            'void' => 'ملغي',
            'cancelled' => 'ملغى',
            'completed' => 'مكتمل',
            default => $status,
        };
    }

    public function statusLabel(): string
    {
        return self::statusLabelFor($this->statusValue());
    }

    public function changeStatus(string $status, ?string $reason = null): self
    {
        $this->update(['status' => $status]);
        $this->setStatus($status, $reason);

        return $this;
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'غير مدفوع',
            'partial' => 'مدفوع جزئياً',
            'paid' => 'مدفوع',
            'refunded' => 'مسترجع',
            'overpaid' => 'مدفوع زائد',
            default => $this->payment_status,
        };
    }

    public function paymentMethodLabel(): string
    {
        $method = data_get($this->meta, 'payment_method');

        if (! $method) {
            return '-';
        }

        return match ($method) {
            'cash' => 'نقداً',
            'card' => 'بطاقة',
            'bank_transfer' => 'تحويل بنكي',
            'online' => 'دفع إلكتروني',
            default => $method,
        };
    }

    public function statusBadgeColor(): string
    {
        return self::statusBadgeColorFor($this->statusValue());
    }

    public static function statusBadgeColorFor(string $status): string
    {
        return match ($status) {
            'completed', 'paid', 'confirmed' => 'green',
            'cancelled', 'void' => 'red',
            'draft', 'open' => 'gray',
            default => 'blue',
        };
    }

    public function paymentStatusBadgeColor(): string
    {
        return match ($this->payment_status) {
            'paid' => 'green',
            'unpaid' => 'red',
            'partial' => 'yellow',
            'refunded' => 'purple',
            default => 'gray',
        };
    }
}
