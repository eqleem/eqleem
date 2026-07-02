<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $content_id
 * @property int|null $client_id
 * @property int|null $block_id
 * @property string $status
 * @property array<string, mixed> $data
 * @property Carbon|null $submitted_at
 */
#[SoftDeletes]
#[Fillable([
    'tenant_id',
    'content_id',
    'client_id',
    'block_id',
    'status',
    'data',
    'submitted_at',
])]
class FormSubmission extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
