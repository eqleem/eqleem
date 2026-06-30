<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'handle', 'user_id', 'theme_id', 'active', 'data', 'meta', 'config', 'phone', 'email', 'status', 'role'])]
#[Hidden(['deleted_at'])]
#[SoftDeletes]
class Tenant extends Model
{
    use HasUuid;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'meta' => 'array',
            'config' => 'array',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLogoAttribute(): string
    {
        $stored = data_get($this->data, 'logo');

        if (Str::startsWith((string) $stored, 'http')) {
            return (string) $stored;
        }

        if (filled($stored)) {
            return Storage::url((string) $stored);
        }

        return 'https://api.dicebear.com/9.x/shapes/svg?seed='.$this->uuid;
    }
}
