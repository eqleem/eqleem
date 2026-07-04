<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\ModelStatus\HasStatuses;

class Invoice extends Model
{
    use HasStatuses, HasUuid;

    protected $fillable = [
        'invoicable_type',
        'invoicable_id',
        'tenant_id',
        'user_id',
        'created_by',
        'amount_paid',
        'total_before_vat',
        'total_after_vat',
        'subtotal_before_vat',
        'subtotal_after_vat',
        'currency',
        'type',
        'initial_status',
        'issued_on',
        'paid_on',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'integer',
            'total_before_vat' => 'integer',
            'total_after_vat' => 'integer',
            'subtotal_before_vat' => 'integer',
            'subtotal_after_vat' => 'integer',
            'issued_on' => 'datetime',
            'paid_on' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->tenant_id = tenant('id');
            $model->number = str_pad(self::max('number') + 1, 6, 0, STR_PAD_LEFT);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getSNumberAttribute()
    {
        return 'INV-'.$this->number;
    }

    public function getCurrentStatusAttribute()
    {
        return $this->status() ? $this->status : $this->initial_status;
    }
}
