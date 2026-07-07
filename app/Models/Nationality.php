<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualName;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    use HasBilingualName;

    protected $connection = 'world';

    public $timestamps = false;

    protected $fillable = [
        'code', 'name_en', 'name_ar', 'active', 'meta',
    ];

    protected $casts = [
        'active' => 'boolean',
        'meta' => 'json',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }
}
