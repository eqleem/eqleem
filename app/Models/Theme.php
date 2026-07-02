<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

#[SoftDeletes]
class Theme extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'config' => 'array',
            'active' => 'boolean',
            'public' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function getImagePathAttribute(): string
    {
        return file_exists(public_path('assets/images/themes/'.$this->slug.'.png')) ? asset('assets/images/themes/'.$this->slug.'.png') :
            'https://api.dicebear.com/10.x/stripes/svg?seed='.data_get($this, 'slug') ?? config('app.name');
    }
}
