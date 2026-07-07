<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualName;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasBilingualName;

    protected $connection = 'world';

    protected $fillable = [
        'id', 'name_en', 'name_ar', 'country_id', 'state_id', 'active', 'meta',
    ];

    protected $casts = [
        'active' => 'boolean',
        'meta' => 'json',
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
