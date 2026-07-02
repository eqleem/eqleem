<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property Carbon|null $read_at
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
    'read_at',
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
            'read_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FormSubmissionReply::class)->latest();
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'new' => 'جديد',
            'read' => 'مقروء',
            'archived' => 'مؤرشف',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'new' => 'blue',
            'read' => 'green',
            'archived' => 'gray',
            default => 'gray',
        };
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->read_at = now();

        if ($this->status === 'new') {
            $this->status = 'read';
        }

        $this->save();
    }

    /**
     * @return list<array{id: string, name: string, label: string, type: string, value: mixed}>
     */
    public function fields(): array
    {
        $fields = data_get($this->data, 'fields', []);

        return is_array($fields) ? array_values($fields) : [];
    }

    public function previewText(): string
    {
        foreach ($this->fields() as $field) {
            $value = $field['value'] ?? null;

            if (is_bool($value)) {
                continue;
            }

            if (filled($value) && $field['type'] !== 'file') {
                return (string) $value;
            }
        }

        return '';
    }

    public function fieldsCount(): int
    {
        return count($this->fields());
    }

    public static function unreadCount(?int $tenantId = null): int
    {
        $query = self::query()->whereNull('read_at');

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->count();
    }
}
