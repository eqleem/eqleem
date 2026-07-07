<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        /**
         * Attach to the 'creating' Model Event to provide a UUID
         * for the `uuid` field
         */
        static::creating(function ($model): void {
            $columnName = static::getUuidColumn();

            if (blank($model->{$columnName})) {
                $model->{$columnName} = (string) Str::uuid();
            }
        });
    }

    public function ensureUuid(): static
    {
        $columnName = static::getUuidColumn();

        if (filled($this->{$columnName})) {
            return $this;
        }

        $this->forceFill([
            $columnName => (string) Str::uuid(),
        ])->save();

        return $this;
    }

    /* Getters and Setters */

    public function getUuidAttribute(): ?string
    {
        $columnName = static::getUuidColumn();

        return $this->attributes[$columnName] ?? null;
    }

    protected static function getUuidColumn()
    {
        if (isset(static::$uuidColumn)) {
            return static::$uuidColumn;
        }

        return 'uuid';
    }
}
