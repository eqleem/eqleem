<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualName;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasBilingualName;

    protected $connection = 'world';

    protected $fillable = [
        'id', 'name_en', 'name_ar', 'iso2', 'active', 'meta',
    ];

    protected $casts = [
        'meta' => 'json',
        'active' => 'boolean',
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Scope a query to only include active Countries.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    /**
     * get Country By iso2 code.
     *
     * @throws \Throwable
     */
    public static function getByIso2(string $iso2)
    {
        $country = static::where('iso2', strtoupper($iso2))->first();
        throw_if(is_null($country), "{$iso2} does not exist");

        return $country;
    }

    /**
     * @throws \Throwable
     */
    public static function getByCode(string $code): self
    {
        return static::getByIso2($code);
    }
}
