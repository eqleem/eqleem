<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MeShaon\RequestAnalytics\Models\RequestAnalytics as BaseRequestAnalytics;

class RequestAnalytics extends BaseRequestAnalytics
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
