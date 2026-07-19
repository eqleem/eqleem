<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean review row for the dashboard.
 *
 * @mixin Review
 */
class ReviewListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Review $review */
        $review = $this->resource;

        return [
            'id' => $review->id,
            'title' => $review->title,
            'score' => $review->score,
            'rating' => $review->rating,
            'published' => $review->published,
            'reviewer' => [
                'registered' => $review->client_id !== null,
                'name' => $review->reviewerName(),
                'email' => $review->reviewerEmail(),
                'phone' => $review->reviewerPhone(),
            ],
            'content' => $review->content ? [
                'id' => $review->content->id,
                'title' => $review->content->title,
                'type' => $review->content->type,
            ] : null,
            'order' => $review->order ? [
                'uuid' => $review->order->uuid,
                'number' => $review->order->number,
            ] : null,
            'branch' => $review->branch ? [
                'id' => $review->branch->id,
                'name' => $review->branch->display_name,
            ] : null,
            'barcode_id' => $review->barcode_id,
            'created' => $review->created_at?->translatedFormat('d M Y'),
            'created_at' => $review->created_at?->toIso8601String(),
        ];
    }
}
