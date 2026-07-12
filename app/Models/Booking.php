<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'content_id',
        'calendar_id',
        'start_at',
        'end_at',
        'status',
        'data',
        'price_snapshot',
        'currency',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'data' => 'array',
            'price_snapshot' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'new' => 'جديد',
            'awaiting_payment' => 'بانتظار الدفع',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
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
            'completed' => 'package',
            'cancelled' => 'x',
        ];
    }

    public static function statusIconFor(string $status): string
    {
        return self::statusIcons()[self::normalizeStatus($status)] ?? 'info';
    }

    /**
     * Legacy `pending` is treated as `new`.
     */
    public static function normalizeStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'new',
            default => $status,
        };
    }

    public static function statusLabelFor(string $status): string
    {
        $normalized = self::normalizeStatus($status);

        return self::statuses()[$normalized] ?? $status;
    }

    public static function statusBadgeColorFor(string $status): string
    {
        return match (self::normalizeStatus($status)) {
            'new' => 'blue',
            'awaiting_payment' => 'yellow',
            'confirmed' => 'teal',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
