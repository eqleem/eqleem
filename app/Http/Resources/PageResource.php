<?php

namespace App\Http\Resources;

use App\Models\Content;
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
            'published' => $content->status === 'published',
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

        return $payload;
    }
}
