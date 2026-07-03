<?php

namespace App\Models;

use App\Support\Money;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status() ?: __($this->initial_status);
    }

    public function getWhoAttribute()
    {
        if ($this->user_id) {
            return $this->user->name.' '.($this->source_name ? '('.$this->source_name.')' : '');
        }

        if ($this->client_id) {
            return $this->client->name.' '.($this->source_name ? '('.$this->source_name.')' : '');
        }

        return '-';
    }

    public function formattedAmount(): string
    {
        return Money::format($this->amount);
    }
}
