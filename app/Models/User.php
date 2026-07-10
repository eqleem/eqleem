<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['name', 'email', 'phone', 'password', 'current_tenant_id', 'image', 'uuid'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, InteractsWithMedia, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'));
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'current_tenant_id');
    }

    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function ownsTenant(Tenant $tenant): bool
    {
        return (int) $tenant->user_id === (int) $this->id;
    }

    public function canAccessDashboard(?Tenant $tenant = null): bool
    {
        $tenant ??= $this->currentTenant;

        return $tenant instanceof Tenant
            && $this->ownsTenant($tenant)
            && (bool) $tenant->active;
    }

    public function getImageAttribute($value)
    {
        if (Str::startsWith($value, 'http')) {
            return $value;
        }

        if ($value) {
            return Storage::url($value);
        }

        return 'https://api.dicebear.com/9.x/thumbs/svg?seed='.data_get($this, 'email') ?? config('app.name');
    }
}
