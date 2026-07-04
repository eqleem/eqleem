<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\MorphTenantable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasUuid, MorphTenantable, Notifiable;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'national_id',
        'address',
        'city',
        'neighborhood',
        'notes',
        'active',
        'meta',
        'email_verified_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'array',
            'email_verified_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->nullable();
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(ClientSocialAccount::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Tenant-scoped profile data from the tenantables pivot.
     *
     * @return array<string, mixed>
     */
    public function profileForTenant(?int $tenantId = null): array
    {
        $tenantId ??= currentTenantId();

        if (! $tenantId) {
            return [];
        }

        $pivot = $this->tenants()
            ->where('tenants.id', $tenantId)
            ->first()
            ?->pivot;

        $meta = is_array($pivot?->meta) ? $pivot->meta : [];

        return [
            'name' => $meta['name'] ?? $this->name,
            'email' => $meta['email'] ?? $this->email,
            'phone' => $meta['phone'] ?? $this->phone,
            'avatar' => $meta['avatar'] ?? data_get($this->meta, 'avatar'),
            'active' => (bool) ($pivot?->active ?? $this->active),
        ];
    }

    public function displayName(?int $tenantId = null): string
    {
        return (string) ($this->profileForTenant($tenantId)['name'] ?? $this->name);
    }

    public function getAvatarAttribute(): string
    {
        $tenantAvatar = $this->profileForTenant()['avatar'] ?? null;

        if (filled($tenantAvatar)) {
            if (str_starts_with((string) $tenantAvatar, 'http')) {
                return (string) $tenantAvatar;
            }

            return \Storage::disk('public')->url((string) $tenantAvatar);
        }

        return data_get($this->meta, 'avatar')
            ? \Storage::disk('public')->url(data_get($this->meta, 'avatar'))
            : 'https://api.dicebear.com/9.x/fun-emoji/svg?seed='.data_get($this, 'id');
    }
}
