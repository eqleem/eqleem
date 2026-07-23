<?php

namespace App\Support;

use App\Models\Content;

/**
 * Builds the view payload for about-template content pages.
 */
class AboutPageView
{
    /**
     * @return array{
     *     page: Content,
     *     subtitle: string,
     *     heroImageUrl: string|null,
     *     primaryButton: array<string, mixed>|null,
     *     stats: list<array{id: string, value: string, label: string}>,
     *     featuresTitle: string,
     *     featuresDescription: string,
     *     features: list<array{id: string, title: string, description: string, brand_mark: array<string, mixed>|null}>
     * }
     */
    public static function make(Content $page): array
    {
        $defaults = Content::defaultAboutPageData();
        $data = is_array($page->data) ? $page->data : [];

        $heroImage = data_get($data, 'hero_image');
        $heroImagePath = filled($heroImage) ? (string) $heroImage : null;

        $stats = collect(data_get($data, 'stats', $defaults['stats']))
            ->filter(fn ($stat): bool => is_array($stat))
            ->values()
            ->map(fn (array $stat): array => [
                'id' => (string) ($stat['id'] ?? ''),
                'value' => (string) ($stat['value'] ?? ''),
                'label' => (string) ($stat['label'] ?? ''),
            ])
            ->filter(fn (array $stat): bool => $stat['value'] !== '')
            ->values()
            ->all();

        $features = collect(data_get($data, 'features', $defaults['features']))
            ->filter(fn ($feature): bool => is_array($feature))
            ->values()
            ->map(fn (array $feature): array => [
                'id' => (string) ($feature['id'] ?? ''),
                'title' => (string) ($feature['title'] ?? ''),
                'description' => (string) ($feature['description'] ?? ''),
                'brand_mark' => BlockBrandMark::forDisplay(
                    is_array($feature['brand_mark'] ?? null) ? $feature['brand_mark'] : null
                ),
            ])
            ->filter(fn (array $feature): bool => $feature['title'] !== '')
            ->values()
            ->all();

        return [
            'page' => $page,
            'subtitle' => (string) data_get($data, 'subtitle', $defaults['subtitle']),
            'heroImageUrl' => $heroImagePath !== null ? contentImageUrl($heroImagePath) : null,
            'primaryButton' => self::resolvePrimaryButton(
                is_array(data_get($data, 'primary_button'))
                    ? data_get($data, 'primary_button')
                    : $defaults['primary_button']
            ),
            'stats' => $stats,
            'featuresTitle' => (string) data_get($data, 'features_title', $defaults['features_title']),
            'featuresDescription' => (string) data_get($data, 'features_description', $defaults['features_description']),
            'features' => $features,
        ];
    }

    /**
     * @param  array<string, mixed>  $button
     * @return array<string, mixed>|null
     */
    protected static function resolvePrimaryButton(array $button): ?array
    {
        $label = trim((string) ($button['label'] ?? ''));

        if ($label === '') {
            return null;
        }

        $linkType = (string) ($button['link_type'] ?? 'external');
        $contentType = (string) ($button['content_type'] ?? '');
        $contentId = filled($button['content_id'] ?? null) ? (int) $button['content_id'] : null;

        $isForm = $linkType === 'form'
            || ($linkType === 'item' && $contentType === 'forms' && $contentId);
        $isBooking = $linkType === 'booking';

        $form = null;
        $formFields = [];
        $formDescription = '';

        if ($isForm && $contentId) {
            $form = Content::query()
                ->type(contentTypeModel('forms'))
                ->whereKey($contentId)
                ->where('active', true)
                ->first(['id', 'data']);

            if (! $form) {
                $isForm = false;
            } else {
                $formFields = FormField::normalize(data_get($form->data, 'fields'));
                $formDescription = (string) data_get(
                    $form->data,
                    'description',
                    'املأ النموذج وسنتواصل معك في أقرب وقت.'
                );
            }
        }

        $bookingConfig = $isBooking ? CtaBooking::configFromData($button) : null;
        $url = ($isForm || $isBooking) ? null : CtaLink::urlFromData($button);

        if (! $isForm && ! $isBooking && ! filled($url)) {
            return null;
        }

        return [
            'id' => 'about-primary',
            'label' => $label,
            'url' => $url,
            'isForm' => $isForm,
            'isBooking' => $isBooking,
            'formContentId' => $isForm ? $contentId : null,
            'opensInNewTab' => $linkType === 'external',
            'formDescription' => $formDescription,
            'formFields' => $formFields,
            'bookingBranchIds' => $bookingConfig['branch_ids'] ?? [],
            'bookingCalendarIds' => $bookingConfig['calendar_ids'] ?? [],
            'bookingAllowClientChoice' => $bookingConfig['allow_client_choice'] ?? true,
            'bookingDurationMinutes' => $bookingConfig['duration_minutes'] ?? 30,
        ];
    }
}
