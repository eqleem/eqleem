<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Support\BlockBrandMark;
use App\Support\CtaBooking;
use App\Support\CtaLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full page payload for the detail editor.
 *
 * @mixin Content
 */
class PageResource extends JsonResource
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
        $data = is_array($content->data) ? $content->data : [];

        $payload = [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'subtitle' => (string) data_get($data, 'subtitle', ''),
            'body' => (string) data_get($data, 'body', ''),
            'editor_mode' => (string) data_get($data, 'editor_mode', 'html'),
            'slug' => $content->slug,
            'template' => $content->template,
            'template_label' => filled($content->template)
                ? (Content::pageTemplateOptions()[$content->template] ?? $content->template)
                : null,
            'is_system_page' => $content->isSystemPage(),
            'active' => (bool) $content->active,
            'status' => $content->status,
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
        ];

        if ($content->template === 'contact') {
            $defaults = Content::defaultContactPageData();
            $formFields = is_array(data_get($data, 'form_fields'))
                ? data_get($data, 'form_fields')
                : [];

            $payload['show_form'] = (bool) data_get($data, 'show_form', $defaults['show_form']);
            $payload['form_fields'] = collect(Content::contactFormFieldKeys())
                ->mapWithKeys(fn (string $field): array => [
                    $field => (bool) data_get($formFields, $field, data_get($defaults['form_fields'], $field, false)),
                ])
                ->all();
            $payload['show_social_links'] = (bool) data_get($data, 'show_social_links', $defaults['show_social_links']);
            $payload['show_contact_info'] = (bool) data_get($data, 'show_contact_info', $defaults['show_contact_info']);
            $payload['show_extra_links'] = (bool) data_get($data, 'show_extra_links', $defaults['show_extra_links']);
            $payload['success_message'] = (string) data_get($data, 'success_message', $defaults['success_message']);
        }

        if ($content->template === 'faq') {
            $payload['faqs'] = collect(data_get($data, 'faqs', []))
                ->filter(fn ($faq): bool => is_array($faq))
                ->values()
                ->map(fn (array $faq): array => [
                    'id' => (string) ($faq['id'] ?? ''),
                    'question' => (string) ($faq['question'] ?? ''),
                    'answer' => (string) ($faq['answer'] ?? ''),
                ])
                ->all();
        }

        if ($content->template === 'about') {
            $defaults = Content::defaultAboutPageData();
            $heroImage = data_get($data, 'hero_image');
            $heroImagePath = filled($heroImage) ? (string) $heroImage : null;

            $payload['hero_image'] = $heroImagePath !== null ? contentImageUrl($heroImagePath) : null;
            $payload['hero_image_path'] = $heroImagePath;
            $payload['primary_button'] = $this->mapAboutPrimaryButton(
                is_array(data_get($data, 'primary_button'))
                    ? data_get($data, 'primary_button')
                    : $defaults['primary_button']
            );
            $payload['stats'] = collect(data_get($data, 'stats', $defaults['stats']))
                ->filter(fn ($stat): bool => is_array($stat))
                ->values()
                ->map(fn (array $stat): array => [
                    'id' => (string) ($stat['id'] ?? ''),
                    'value' => (string) ($stat['value'] ?? ''),
                    'label' => (string) ($stat['label'] ?? ''),
                ])
                ->all();
            $payload['features_title'] = (string) data_get($data, 'features_title', $defaults['features_title']);
            $payload['features_description'] = (string) data_get($data, 'features_description', $defaults['features_description']);
            $payload['features'] = collect(data_get($data, 'features', []))
                ->filter(fn ($feature): bool => is_array($feature))
                ->values()
                ->map(fn (array $feature): array => [
                    'id' => (string) ($feature['id'] ?? ''),
                    'title' => (string) ($feature['title'] ?? ''),
                    'description' => (string) ($feature['description'] ?? ''),
                    'brand_mark' => BlockBrandMark::forEditor(
                        is_array($feature['brand_mark'] ?? null) ? $feature['brand_mark'] : null
                    ),
                ])
                ->all();
            $payload['link_type_picker_options'] = CtaLink::blockLinkPickerOptions();
            $payload['booking_targets'] = [
                'branches' => CtaBooking::branchOptions(),
                'calendars' => CtaBooking::calendarOptions(),
            ];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $button
     * @return array<string, mixed>
     */
    protected function mapAboutPrimaryButton(array $button): array
    {
        $defaults = Content::defaultAboutPrimaryButton();
        $merged = array_merge($defaults, $button);
        $contentId = filled($merged['content_id'] ?? null) ? (int) $merged['content_id'] : null;

        return [
            'label' => (string) ($merged['label'] ?? ''),
            'link_type' => CtaLink::typeKeyFromStoredData($merged),
            'content_type' => filled($merged['content_type'] ?? null) ? (string) $merged['content_type'] : null,
            'content_id' => $contentId,
            'selected_content_title' => $contentId
                ? (Content::query()->find($contentId)?->title ?? '')
                : '',
            'url' => filled($merged['url'] ?? null) ? (string) $merged['url'] : '',
            'branch_ids' => collect($merged['branch_ids'] ?? [])
                ->map(fn ($id): int => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->values()
                ->all(),
            'calendar_ids' => collect($merged['calendar_ids'] ?? [])
                ->map(fn ($id): int => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->values()
                ->all(),
            'allow_client_choice' => (bool) ($merged['allow_client_choice'] ?? true),
            'duration_minutes' => max(1, (int) ($merged['duration_minutes'] ?? 30)),
        ];
    }
}
