<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard newsletter issues table.
 *
 * @mixin Content
 */
class NewsletterListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $imagePath = data_get($content->data, 'image');
        $mailStatus = $content->newsletterMailStatus();
        $displayDate = $content->newsletterSentAt() ?? $content->newsletterScheduledAt();

        $dateLabel = match ($mailStatus) {
            'sent' => 'أُرسلت',
            'scheduled' => 'مجدولة',
            default => null,
        };

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'subject' => (string) data_get($content->data, 'subject', ''),
            'mail_status' => $mailStatus,
            'mail_status_label' => $content->newsletter_mail_status_label,
            'display_date' => $displayDate?->toIso8601String(),
            'display_date_label' => $displayDate?->translatedFormat('j M Y، H:i'),
            'date_kind_label' => $dateLabel,
            'recipients_count' => $content->newsletterRecipientsCount(),
            'status' => $content->status,
            'status_label' => $content->status === 'published' ? 'منشورة' : 'مسودة',
            'image' => filled($imagePath) ? (contentImageUrl((string) $imagePath) ?? (string) $imagePath) : null,
        ];
    }
}
