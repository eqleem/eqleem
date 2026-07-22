<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'booking_id',
        'sku',
        'name',
        'qty',
        'unit_price',
        'discount_total',
        'tax_total',
        'line_total',
        'tax_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'qty' => 'integer',
            'unit_price' => 'integer',
            'discount_total' => 'integer',
            'tax_total' => 'integer',
            'line_total' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function formattedUnitPrice(): string
    {
        return Money::format($this->unit_price);
    }

    public function formattedLineTotal(): string
    {
        return Money::format($this->line_total);
    }

    public function typeLabel(): string
    {
        $type = (string) data_get($this->meta, 'type', 'other');

        return Order::itemTypeOptions()[$type] ?? $type;
    }
}
