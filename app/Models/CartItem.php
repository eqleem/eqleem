<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'productable_id',
        'productable_type',
        'quantity',
        'unit_price',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'meta' => 'array',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lineTotal(): int
    {
        return $this->quantity * $this->unit_price;
    }

    public function itemType(): string
    {
        return (string) data_get($this->meta, 'item_type', 'other');
    }

    public function title(): string
    {
        return (string) data_get($this->meta, 'title', '');
    }

    public function imageUrl(): ?string
    {
        return data_get($this->meta, 'image_url');
    }

    public function slug(): ?string
    {
        return data_get($this->meta, 'slug');
    }
}
