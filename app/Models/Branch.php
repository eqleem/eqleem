<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

#[Fillable([
    'tenant_id',
    'city_id',
    'name',
    'city',
    'country',
    'lat',
    'long',
    'type',
    'address',
    'street',
    'district',
    'building_number',
    'extra_number',
    'postal_code',
    'phone',
    'phonecode',
    'email',
    'active',
    'is_warehouse',
    'is_pickup',
    'config',
    'order',
])]
class Branch extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'config' => SchemalessAttributes::class,
            'active' => 'boolean',
            'is_warehouse' => 'boolean',
            'is_pickup' => 'boolean',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;

        if (is_array($name)) {
            return (string) ($name[app()->getLocale()] ?? $name['ar'] ?? reset($name) ?: '');
        }

        return (string) $name;
    }

    public function getCountryLabelAttribute(): string
    {
        return (string) (config('verification.countries')[$this->country] ?? $this->country);
    }

    public function getLocationSummaryAttribute(): string
    {
        return collect([$this->country_label, $this->city])
            ->filter()
            ->implode(' , ');
    }

    public function getNameWithCityAttribute(): string
    {
        return $this->display_name." ({$this->city})";
    }

    public function getPhonenumberAttribute(): string
    {
        return (string) $this->phonecode.$this->phone;
    }

    /**
     * @return array<string, array{enabled: bool, start: string, end: string}>
     */
    public function workingHours(): array
    {
        $saved = $this->config?->get('working_hours');

        return Calendar::normalizeAvailabilities(is_array($saved) ? $saved : null);
    }

    /**
     * @param  array<string, array{enabled: bool, start: string, end: string}>  $workingHours
     */
    public function setWorkingHours(array $workingHours): void
    {
        $this->config->set('working_hours', Calendar::normalizeAvailabilities($workingHours));
    }

    /**
     * @return array<string, string>
     */
    public static function localizedName(string $name): array
    {
        return ['ar' => $name];
    }
}
