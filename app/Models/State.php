<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $connection = 'world';

    protected $fillable = [
        'id', 'name', 'country_id', 'latitude', 'longitude', 'active', 'translations',
    ];

    protected $casts = [
        'active' => 'boolean',
        'translations' => 'json',
        'meta' => 'json',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
