<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full newsletter issue payload for the detail editor.
 *
 * @mixin Content
 */
class NewsletterResource extends JsonResource
{
    /**
     * @param  array<string, mixed>  $additional
     */
    public function __construct($resource, protected array $extra = [])
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $imagePath = data_get($content->data, 'image');
        $scheduledAt = $content->newsletterScheduledAt();
        $sentAt = $content->newsletterSentAt();

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'subject' => (string) data_get($content->data, 'subject', ''),
            'subtitle' => (string) data_get($content->data, 'subtitle', ''),
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'slug' => $content->slug,
            'featured_image' => filled($imagePath) ? (contentImageUrl((string) $imagePath) ?? (string) $imagePath) : null,
            'mail_status' => $content->newsletterMailStatus(),
            'mail_status_label' => $content->newsletter_mail_status_label,
            'scheduled_date' => $scheduledAt?->format('Y-m-d'),
            'scheduled_time' => $scheduledAt?->format('H:i'),
            'recipients_count' => $content->newsletterRecipientsCount(),
            'sent_at' => $sentAt?->toIso8601String(),
            'sent_at_label' => $sentAt?->translatedFormat('j F Y، H:i'),
            'status' => $content->status,
            'published' => $content->status === 'published',
            'published_at' => $content->published_at?->toIso8601String(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'mail_status_options' => $this->extra['mail_status_options'] ?? Content::newsletterMailStatusOptions(),
        ];
    }
}
