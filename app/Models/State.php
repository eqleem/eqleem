<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualName;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasBilingualName;

    protected $connection = 'world';

    protected $fillable = [
        'id', 'name_en', 'name_ar', 'code', 'country_id', 'active', 'meta',
    ];

    protected $casts = [
        'active' => 'boolean',
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
