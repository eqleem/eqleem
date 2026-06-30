<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function bootBelongsToUser()
    {
        // save
        static::creating(function (self $model) {
            if ($userId = auth()->id()) {
                if (is_null($model->user_id)) {
                    $model->user_id = $userId;
                }
            }
        });
    }
}
