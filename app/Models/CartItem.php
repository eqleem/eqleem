<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function isBooking(): bool
    {
        return Order::isBookingItemType($this->itemType())
            && filled($this->bookingStartAt())
            && filled($this->bookingEndAt());
    }

    public function hasMealOptions(): bool
    {
        return filled(data_get($this->meta, 'options_signature'));
    }

    public function mealOptionsLabel(): ?string
    {
        $label = data_get($this->meta, 'meal_options_label');

        return filled($label) ? (string) $label : null;
    }

    public function calendarId(): ?int
    {
        $calendarId = data_get($this->meta, 'calendar_id');

        return filled($calendarId) ? (int) $calendarId : null;
    }

    public function bookingStartAt(): ?string
    {
        $value = data_get($this->meta, 'booking_start_at');

        return filled($value) ? (string) $value : null;
    }

    public function bookingEndAt(): ?string
    {
        $value = data_get($this->meta, 'booking_end_at');

        return filled($value) ? (string) $value : null;
    }

    public function bookingDateLabel(): ?string
    {
        $startAt = $this->bookingStartAt();

        return filled($startAt)
            ? Carbon::parse($startAt)->translatedFormat('l j F Y')
            : null;
    }

    public function bookingTimeLabel(): ?string
    {
        if ($this->itemType() === 'unit_rental') {
            $checkIn = data_get($this->meta, 'check_in');
            $checkOut = data_get($this->meta, 'check_out');

            if (filled($checkIn) && filled($checkOut)) {
                return Carbon::parse($checkIn)->translatedFormat('j M Y').' — '.Carbon::parse($checkOut)->translatedFormat('j M Y');
            }
        }

        $startAt = $this->bookingStartAt();
        $endAt = $this->bookingEndAt();

        if (blank($startAt) || blank($endAt)) {
            return null;
        }

        return Carbon::parse($startAt)->format('H:i').' - '.Carbon::parse($endAt)->format('H:i');
    }
}
