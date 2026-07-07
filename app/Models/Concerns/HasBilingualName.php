<?php

namespace App\Models\Concerns;

trait HasBilingualName
{
    public function getNameAttribute(): ?string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return $this->attributes['name_ar'] ?? $this->attributes['name_en'] ?? null;
        }

        return $this->attributes['name_en'] ?? $this->attributes['name_ar'] ?? null;
    }
}
