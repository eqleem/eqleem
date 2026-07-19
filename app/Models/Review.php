<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'content_id',
    'client_id',
    'order_id',
    'branch_id',
    'barcode_id',
    'title',
    'score',
    'rating',
    'name',
    'email',
    'phone',
    'published',
    'meta',
])]
class Review extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'published' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewerName(): string
    {
        return (string) ($this->client?->name ?: $this->name ?: 'زائر');
    }

    public function reviewerEmail(): ?string
    {
        return $this->client?->email ?: $this->email;
    }

    public function reviewerPhone(): ?string
    {
        return $this->client?->phone ?: $this->phone;
    }
}
