<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $connection = 'world';

    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'name_native', 'dir', 'active', 'meta',
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
