<?php

namespace App\Models;

use App\Services\CheckoutShippingService;
use App\Support\Money;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'new' => 'جديد',
            'awaiting_payment' => 'بانتظار الدفع',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'refunded' => 'مسترد',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusIcons(): array
    {
        return [
            'new' => 'sparkles',
            'awaiting_payment' => 'coin',
            'confirmed' => 'check',
            'processing' => 'refresh',
            'completed' => 'package',
            'cancelled' => 'x',
            'refunded' => 'history',
        ];
    }

    public static function statusIconFor(string $status): string
    {
        return self::statusIcons()[$status] ?? 'info';
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
            'digital_product' => 'منتج رقمي',
            'course' => 'دورة',
            'digital_service' => 'خدمة رقمية',
            'on_demand_service' => 'خدمة حسب الطلب',
            'menu' => 'صنف طعام',
            'service' => 'خدمة',
            'unit_rental' => 'وحدة تأجير',
            'other' => 'أخرى',
        ];
    }

    /**
     * @return list<string>
     */
    public static function bookingItemTypes(): array
    {
        return ['service', 'unit_rental'];
    }

    public static function isBookingItemType(string $type): bool
    {
        return in_array($type, self::bookingItemTypes(), true);
    }

    /**
     * @return list<string>
     */
    public static function shippableItemTypes(): array
    {
        return ['product', 'menu'];
    }

    public static function isShippableItemType(string $type): bool
    {
        return in_array($type, self::shippableItemTypes(), true);
    }

    /**
     * @return array<string, string>
     */
    public static function itemTypeIcons(): array
    {
        return [
            'product' => 'shopping-cart',
            'digital_product' => 'file-download',
            'course' => 'school-bell',
            'digital_service' => 'cloud-computing',
            'on_demand_service' => 'ruler',
            'menu' => 'chef-hat',
            'service' => 'hotel-service',
            'unit_rental' => 'building-estate',
            'other' => 'square-rounded-plus',
        ];
    }

    public function formatAmount(int|string|null $amount): string
    {
        return self::formatMinor($amount);
    }

    public function formatMoney(int|string|null $amount): HtmlString
    {
        return Money::displayWithCurrency($amount, $this->currency_code);
    }

    public function formattedGrandTotal(): HtmlString
    {
        return $this->formatMoney($this->grand_total);
    }

    public function statusValue(): string
    {
        return (string) ($this->getAttributes()['status'] ?? '');
    }

    public static function statusLabelFor(string $status): string
    {
        return match ($status) {
            'new', 'draft' => 'جديد',
            'awaiting_payment', 'open' => 'بانتظار الدفع',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled', 'void' => 'ملغي',
            'refunded' => 'مسترد',
            'partially_paid' => 'مدفوع جزئياً',
            'paid' => 'مدفوع',
            default => self::statusOptions()[$status] ?? $status,
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

        if ($method === 'free') {
            return 'طلب مجاني';
        }

        $configuredName = config("payment-methods.{$method}.name");

        if (is_string($configuredName) && filled($configuredName)) {
            return $configuredName;
        }

        return match ($method) {
            'cash', 'cod', 'cash-on-delivery' => 'الدفع عند الاستلام',
            'card', 'credit-card' => 'البطاقة الائتمانية',
            'apple_pay' => 'Apple Pay',
            'bank_transfer', 'bank-transfer' => 'تحويل بنكي',
            'online' => 'دفع إلكتروني',
            'custom' => 'دفع مخصص',
            'tabby' => 'تابي',
            'tamara' => 'تمارا',
            default => (string) $method,
        };
    }

    public function shippingMethodLabel(): string
    {
        $storedLabel = data_get($this->meta, 'shipping_method_label');

        if (filled($storedLabel)) {
            return (string) $storedLabel;
        }

        $method = data_get($this->meta, 'shipping_method');

        if (! $method || $method === 'none') {
            return '-';
        }

        return app(CheckoutShippingService::class)->label((string) $method);
    }

    public function channelLabel(): string
    {
        return match ($this->channel) {
            'ecommerce' => 'المتجر الإلكتروني',
            'manual' => 'إدخال يدوي',
            'pos' => 'نقطة البيع',
            default => filled($this->channel) ? (string) $this->channel : '-',
        };
    }

    public function shippingFee(): int
    {
        return (int) data_get($this->meta, 'shipping_fee', 0);
    }

    public function shippingAddressLabel(): ?string
    {
        $address = data_get($this->meta, 'shipping_address');

        if (blank($address)) {
            return null;
        }

        if (is_string($address)) {
            return $address;
        }

        if (! is_array($address)) {
            return null;
        }

        $parts = collect([
            data_get($address, 'address'),
            data_get($address, 'neighborhood'),
            data_get($address, 'city_label'),
            data_get($address, 'country_label'),
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): string => (string) $value)
            ->values();

        return $parts->isEmpty() ? null : $parts->implode('، ');
    }

    public function statusBadgeColor(): string
    {
        return self::statusBadgeColorFor($this->statusValue());
    }

    public static function statusBadgeColorFor(string $status): string
    {
        return match ($status) {
            'new', 'draft' => 'blue',
            'awaiting_payment', 'open' => 'yellow',
            'confirmed' => 'teal',
            'processing' => 'purple',
            'completed', 'paid' => 'green',
            'cancelled', 'void' => 'red',
            'refunded' => 'pink',
            default => 'gray',
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
