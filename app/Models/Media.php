<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use BelongsToTenant, BelongsToUser;

    protected $fillable = ['tenant_id', 'user_id', 'uuid'];

    protected static function booted(): void
    {
        static::creating(function (Media $media): void {
            if ($media->tenant_id !== null) {
                return;
            }

            $modelClass = Relation::getMorphedModel($media->model_type) ?? $media->model_type;

            if (! is_string($modelClass) || ! class_exists($modelClass) || ! is_a($modelClass, EloquentModel::class, true)) {
                return;
            }

            try {
                /** @var EloquentModel|null $owner */
                $owner = $modelClass::query()->find($media->model_id);
            } catch (\Throwable) {
                return;
            }

            if ($owner === null) {
                return;
            }

            if ($owner instanceof Tenant) {
                $media->tenant_id = $owner->getKey();

                return;
            }

            if (filled($owner->tenant_id ?? null)) {
                $media->tenant_id = (int) $owner->tenant_id;
            }
        });
    }
}
