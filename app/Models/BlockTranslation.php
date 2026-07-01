<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $block_id
 * @property string $locale
 * @property string|null $title
 * @property string|null $slug
 * @property array<string, mixed>|null $data
 */
#[Fillable([
    'block_id',
    'locale',
    'title',
    'slug',
    'data',
])]
class BlockTranslation extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
