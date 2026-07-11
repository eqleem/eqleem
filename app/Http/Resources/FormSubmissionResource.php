<?php

namespace App\Http\Resources;

use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Form submission detail payload.
 *
 * @mixin FormSubmission
 */
class FormSubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var FormSubmission $submission */
        $submission = $this->resource;
        $submittedAt = $submission->submitted_at ?? $submission->created_at;
        $fields = $submission->fields();
        $client = $submission->client;

        return [
            'id' => $submission->id,
            'unread' => $submission->isUnread(),
            'status' => $submission->status,
            'status_label' => $submission->statusLabel(),
            'status_color' => $submission->statusBadgeColor(),
            'fields_count' => count($fields),
            'replies_count' => $submission->relationLoaded('replies') ? $submission->replies->count() : 0,
            'submitted' => $submittedAt?->translatedFormat('d M Y'),
            'submitted_time' => $submittedAt?->translatedFormat('h:i A'),
            'read_at' => $submission->read_at?->translatedFormat('d M Y'),
            'form' => $submission->form ? [
                'id' => $submission->form->id,
                'uuid' => $submission->form->uuid,
                'title' => $submission->form->title,
            ] : null,
            'client' => $client ? [
                'id' => $client->id,
                'uuid' => $client->uuid,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'avatar' => filled(data_get($client->meta, 'avatar'))
                    ? (str_starts_with((string) data_get($client->meta, 'avatar'), 'http')
                        ? (string) data_get($client->meta, 'avatar')
                        : Storage::disk('public')->url((string) data_get($client->meta, 'avatar')))
                    : 'https://api.dicebear.com/9.x/fun-emoji/svg?seed='.$client->id,
            ] : null,
            'fields' => $fields,
            'replies' => $submission->relationLoaded('replies')
                ? $submission->replies->map(fn ($reply) => [
                    'id' => $reply->id,
                    'body' => $reply->body,
                    'user' => $reply->user?->name,
                    'created' => $reply->created_at?->translatedFormat('d M Y h:i A'),
                ])->values()->all()
                : [],
        ];
    }
}
