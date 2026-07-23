<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'user_id',
    'name',
    'parent_id',
    'branch_id',
    'type',
    'timezone',
    'from',
    'to',
    'active',
    'forever',
    'availabilities',
    'special_dates',
    'off_dates',
    'meta',
    'clients_number',
])]
class Calendar extends Model
{
    use BelongsToTenant, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'from' => 'date',
            'to' => 'date',
            'active' => 'boolean',
            'forever' => 'boolean',
            'availabilities' => 'array',
            'special_dates' => 'array',
            'off_dates' => 'array',
            'meta' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookables(): HasMany
    {
        return $this->hasMany(Bookable::class);
    }

    public function services(): MorphToMany
    {
        return $this->morphedByMany(Content::class, 'bookable', 'bookables')
            ->withPivot(['type', 'active'])
            ->withTimestamps();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            'service-provider' => 'مقدم خدمة',
            'provider' => 'مقدم خدمة',
            'place' => 'مكان',
            'tool' => 'أداة',
            'rental-unit' => 'وحدة تأجير',
        ];
    }

    /**
     * @return array<string, array{enabled: bool, start: string, end: string}>
     */
    public static function defaultAvailabilities(): array
    {
        return collect(self::weekdayKeys())
            ->mapWithKeys(fn (string $day): array => [
                $day => [
                    'enabled' => in_array($day, ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'], true),
                    'start' => '08:00',
                    'end' => '17:00',
                ],
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function weekdayKeys(): array
    {
        return ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    }

    /**
     * @return array<string, string>
     */
    public static function weekdayLabels(): array
    {
        return [
            'sunday' => 'الأحد',
            'monday' => 'الإثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
            'saturday' => 'السبت',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $availabilities
     * @return array<string, array{enabled: bool, start: string, end: string}>
     */
    public static function normalizeAvailabilities(?array $availabilities): array
    {
        $defaults = self::defaultAvailabilities();

        foreach ($defaults as $day => $default) {
            $saved = is_array($availabilities[$day] ?? null) ? $availabilities[$day] : [];

            $defaults[$day] = [
                'enabled' => (bool) ($saved['enabled'] ?? $default['enabled']),
                'start' => (string) ($saved['start'] ?? $default['start']),
                'end' => (string) ($saved['end'] ?? $default['end']),
            ];
        }

        return $defaults;
    }
}
