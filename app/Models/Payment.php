<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Traits\BelongsToTenant;
use App\Models\User;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Payment extends Model
{
    use HasUuid, BelongsToTenant, HasStatuses, SoftDeletes;

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
            return $this->user->name . ' ' . ($this->source_name ? '(' . $this->source_name . ')' : '');
        }

        if ($this->client_id) {
            return $this->client->name . ' ' . ($this->source_name ? '(' . $this->source_name . ')' : '');
        }

        return '-';
    }
}
