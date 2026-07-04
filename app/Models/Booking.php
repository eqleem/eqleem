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
        'service_id',
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
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'cancelled' => 'ملغي',
            'completed' => 'مكتمل',
        ];
    }
}
