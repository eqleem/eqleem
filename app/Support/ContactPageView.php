<?php

namespace App\Support;

use App\Models\Content;
use App\Models\Tenant;
use App\Services\TenantProfileService;

/**
 * Builds the view payload for contact-template content pages.
 */
class ContactPageView
{
    /**
     * @return array{
     *     page: Content,
     *     subtitle: string,
     *     showForm: bool,
     *     formFields: array<string, bool>,
     *     showSocialLinks: bool,
     *     showContactInfo: bool,
     *     showExtraLinks: bool,
     *     showFaqLink: bool,
     *     faqUrl: string|null,
     *     showReviewsLink: bool,
     *     successMessage: string,
     *     phone: string,
     *     phoneDial: string,
     *     email: string,
     *     whatsappUrl: string|null,
     *     socialLinks: list<array{name: string, icon: string, url: string}>
     * }
     */
    public static function make(Content $page, ?Tenant $tenant = null): array
    {
        $tenant ??= tenant();
        $defaults = Content::defaultContactPageData();
        $data = is_array($page->data) ? $page->data : [];
        $storedFields = is_array(data_get($data, 'form_fields'))
            ? data_get($data, 'form_fields')
            : [];

        $formFields = collect(Content::contactFormFieldKeys())
            ->mapWithKeys(fn (string $field): array => [
                $field => (bool) data_get(
                    $storedFields,
                    $field,
                    data_get($defaults['form_fields'], $field, false),
                ),
            ])
            ->all();

        $contact = $tenant
            ? app(TenantProfileService::class)->contact($tenant)
            : ['phone' => '', 'email' => '', 'whatsapp' => ''];

        $phone = (string) ($contact['phone'] ?? '');
        $email = (string) ($contact['email'] ?? '');
        $whatsapp = (string) ($contact['whatsapp'] ?? '');
        $phoneDial = self::dialableNumber($phone);
        $whatsappDial = self::dialableNumber($whatsapp !== '' ? $whatsapp : $phone);

        $showExtraLinks = (bool) data_get($data, 'show_extra_links', $defaults['show_extra_links']);
        $faqPage = self::activeFaqPage();

        return [
            'page' => $page,
            'subtitle' => (string) data_get($data, 'subtitle', $defaults['subtitle']),
            'showForm' => (bool) data_get($data, 'show_form', $defaults['show_form']),
            'formFields' => $formFields,
            'showSocialLinks' => (bool) data_get($data, 'show_social_links', $defaults['show_social_links']),
            'showContactInfo' => (bool) data_get($data, 'show_contact_info', $defaults['show_contact_info']),
            'showExtraLinks' => $showExtraLinks,
            'showFaqLink' => $showExtraLinks && $faqPage !== null,
            'faqUrl' => $faqPage !== null
                ? route('tenant.page.detail', ['slug' => $faqPage->slug])
                : null,
            'showReviewsLink' => $showExtraLinks && self::hasEnabledReviewsSection($tenant),
            'successMessage' => (string) data_get($data, 'success_message', $defaults['success_message']),
            'phone' => $phone,
            'phoneDial' => $phoneDial,
            'email' => $email,
            'whatsappUrl' => $whatsappDial !== '' ? 'https://wa.me/'.$whatsappDial : null,
            'socialLinks' => self::socialLinks($tenant),
        ];
    }

    protected static function activeFaqPage(): ?Content
    {
        return Content::query()
            ->type(contentTypeModel('pages'))
            ->template('faq')
            ->published()
            ->where('active', true)
            ->orderBy('id')
            ->first();
    }

    protected static function hasEnabledReviewsSection(?Tenant $tenant): bool
    {
        if (! $tenant) {
            return false;
        }

        if (app(ContentTypeRegistry::class)->findActive('reviews') !== null) {
            return true;
        }

        $enabled = data_get($tenant->config, 'enabled_content_types');

        if (! is_array($enabled)) {
            return false;
        }

        return in_array('reviews', $enabled, true) || in_array('review', $enabled, true);
    }

    protected static function dialableNumber(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * @return list<array{name: string, icon: string, url: string}>
     */
    protected static function socialLinks(?Tenant $tenant): array
    {
        if (! $tenant) {
            return [];
        }

        $networks = config('social-networks', []);

        return app(TenantProfileService::class)
            ->socialLinks($tenant)
            ->map(function (array $link) use ($networks): ?array {
                $networkKey = (string) ($link['network'] ?? '');
                $network = $networks[$networkKey] ?? null;
                $url = (string) ($link['url'] ?? '');

                if (! $network || $url === '') {
                    return null;
                }

                return [
                    'name' => (string) ($network['label'] ?? $networkKey),
                    'icon' => (string) ($network['icon'] ?? 'ri:global-line'),
                    'url' => SocialNetworkUrl::resolve($networkKey, $url),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
