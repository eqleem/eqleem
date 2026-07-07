<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $connection = 'world';

    protected $fillable = [
        'id', 'name', 'country_id', 'state_id',
        'latitude', 'longitude', 'active', 'translations',
    ];

    protected $casts = [
        'active' => 'boolean',
        'translations' => 'json',
    ];

    /**
     * Scope a query to only include active Cities.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
