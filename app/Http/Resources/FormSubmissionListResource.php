<?php

namespace App\Http\Resources;

use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean form submission row.
 *
 * @mixin FormSubmission
 */
class FormSubmissionListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var FormSubmission $submission */
        $submission = $this->resource;
        $submittedAt = $submission->submitted_at ?? $submission->created_at;

        return [
            'id' => $submission->id,
            'unread' => $submission->isUnread(),
            'status' => $submission->status,
            'status_label' => $submission->statusLabel(),
            'status_color' => $submission->statusBadgeColor(),
            'form_title' => $submission->form?->title,
            'preview' => $submission->previewText() ?: null,
            'client' => $submission->client ? [
                'name' => $submission->client->name,
                'email' => $submission->client->email,
                'phone' => $submission->client->phone,
            ] : null,
            'submitted' => $submittedAt?->translatedFormat('d M Y'),
            'time' => $submittedAt?->translatedFormat('h:i A'),
        ];
    }
}
