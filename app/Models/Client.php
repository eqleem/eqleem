<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\MorphTenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasUuid, MorphTenantable;

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
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'array',
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

    // public function bookings(): HasMany
    // {
    //     return $this->hasMany(Booking::class);
    // }

    // public function formSubmissions(): HasMany
    // {
    //     return $this->hasMany(FormSubmission::class);
    // }

    // public function orders(): HasMany
    // {
    //     return $this->hasMany(Order::class);
    // }

    // public function salesDocuments(): HasMany
    // {
    //     return $this->hasMany(SalesDocument::class);
    // }

    // public function contacts(): HasMany
    // {
    //     return $this->hasMany(Contact::class);
    // }

    public function getAvatarAttribute(): string
    {
        return data_get($this->meta, 'avatar') ? \Storage::disk('public')->url(data_get($this->meta, 'avatar')) : 'https://api.dicebear.com/9.x/fun-emoji/svg?seed='.data_get($this, 'id');
    }
}
