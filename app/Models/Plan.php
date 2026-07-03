<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use LucasDotVin\Soulbscription\Models\Plan as SoulbscriptionPlan;

class Plan extends SoulbscriptionPlan
{
    protected $fillable = [
        'grace_days',
        'name',
        'periodicity_type',
        'periodicity',
        'slug',
        'label',
        'price',
        'is_system',
        'active',
        'is_featured',
        'tenant_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_system' => 'boolean',
            'active' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'integer',
        ];
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true)->where('active', true);
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    public function tier(): string
    {
        return (string) (explode('-', $this->slug ?? $this->name)[0] ?? $this->name);
    }

    public function billingLabel(): string
    {
        if ($this->isFree()) {
            return 'مجانية';
        }

        return match ($this->periodicity_type) {
            'Year' => 'سنوياً',
            'Month' => 'شهرياً',
            default => '',
        };
    }

    public function formattedPrice(): string
    {
        return Money::format($this->price);
    }
}
