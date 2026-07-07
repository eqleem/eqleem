<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $connection = 'world';

    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'name_native', 'dir', 'active', 'translations', 'meta',
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
}
