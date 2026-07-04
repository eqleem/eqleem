<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSocialAccount extends Model
{
    protected $table = 'clients_social';

    protected $fillable = [
        'client_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
