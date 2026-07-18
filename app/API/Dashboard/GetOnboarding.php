<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\ContentTypeRegistry;
use App\Support\Onboarding;
use App\Support\SocialNetworkUrl;
use App\Support\TenantThemeOptions;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns the dashboard onboarding boarding payload and step progress.
 */
class GetOnboarding
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, Onboarding $onboarding): array
    {
        $progress = $onboarding->forTenant($tenant);
        $profile = app(TenantProfileService::class);

        $tenant->loadMissing('theme');
        $themeId = (int) ($tenant->theme_id ?? 0);
        $themeSlug = $tenant->theme?->slug ?? 'default';
        $schema = app(TenantThemeOptions::class)->schemaForTheme($themeSlug);
        $themeOptions = $themeId > 0 ? $tenant->themeSettingsFor($themeId) : [];

        $enabledContentTypes = data_get($tenant->config, 'enabled_content_types');
        $enabledContentTypes = is_array($enabledContentTypes) ? array_values($enabledContentTypes) : [];
        $sellableSlugs = app(ContentTypeRegistry::class)->configured()
            ->filter(fn ($type): bool => $type->sellable)
            ->pluck('slug');
        $enabledCatalogContentTypes = collect($enabledContentTypes)
            ->filter(fn (mixed $slug): bool => is_string($slug) && $sellableSlugs->contains($slug))
            ->values()
            ->all();

        $catalogOptions = app(ContentTypeRegistry::class)->configured()
            ->filter(fn ($type): bool => $type->sellable)
            ->map(fn ($type): array => [
                'slug' => $type->slug,
                'name' => $type->name,
                'description' => $type->description,
                'icon' => $type->icon,
                'icon_url' => asset($type->icon),
                'color' => $type->color,
                'enabled' => in_array($type->slug, $enabledContentTypes, true)
                    || ($enabledContentTypes === [] && $type->active),
            ])
            ->values()
            ->all();

        $contact = $profile->contact($tenant);
        $country = (string) ($contact['country'] ?? '');
        $headerImage = (string) data_get($themeOptions, 'headerImage', data_get($schema, 'headerImage.default', ''));
        $headerImageUrl = $this->resolveHeaderImageUrl($tenant, $headerImage);
        $primaryColor = (string) data_get($themeOptions, 'primaryColor', data_get($schema, 'primaryColor.default', 'blue'));
        $primaryPalette = app(TenantThemeOptions::class)->primaryPalette(['primaryColor' => $primaryColor]);

        $socialLinks = $profile->socialLinks($tenant)
            ->map(function (array $link): array {
                $url = (string) ($link['url'] ?? '');
                $network = (string) ($link['network'] ?? '');

                return [
                    ...$link,
                    'username' => $this->extractSocialUsername($network, $url),
                    'url' => SocialNetworkUrl::resolve($network, $url),
                ];
            })
            ->values()
            ->all();

        $industryOptions = collect(config('industries', []))
            ->map(fn (array|string $industry, string $slug): array => [
                'slug' => $slug,
                'label' => is_array($industry) ? (string) ($industry['label'] ?? $slug) : $industry,
                'emoji' => is_array($industry) ? (string) ($industry['emoji'] ?? '✨') : '✨',
                'description' => is_array($industry) ? (string) ($industry['description'] ?? '') : '',
            ])
            ->values()
            ->all();

        $actionOptions = collect(config('onboarding-actions', []))
            ->map(fn (array $action, string $type): array => [
                'type' => $type,
                'label' => (string) ($action['label'] ?? $type),
                'description' => (string) ($action['description'] ?? ''),
                'icon' => (string) ($action['icon'] ?? 'hugeicons:link-04'),
            ])
            ->values()
            ->all();

        return [
            'percentage' => $progress['percentage'],
            'completed_steps' => $progress['completed'],
            'total_steps' => $progress['total'],
            'current_step' => $progress['current_step'],
            'completed' => $progress['percentage'] >= 100,
            'dismissed' => $progress['dismissed'],
            'page_url' => $tenant->url,
            'steps' => $progress['steps']->values()->all(),
            'forms' => [
                'business' => [
                    'industry' => (string) data_get($tenant->meta, 'industry', ''),
                    'name' => (string) ($tenant->name ?? ''),
                    'bio' => $profile->bio($tenant),
                    'logo' => $profile->hasLogo($tenant) ? $profile->logo($tenant) : '',
                    'brand_mark' => $profile->hasLogo($tenant) ? $profile->brandMark($tenant) : null,
                ],
                'contact' => [
                    ...$contact,
                    'country' => preg_match('/^[A-Za-z]{2}$/', $country) === 1
                        ? strtoupper($country)
                        : 'SA',
                    'social_links' => $socialLinks,
                ],
                'identity' => [
                    'theme_id' => $themeId > 0 ? $themeId : null,
                    'handle' => (string) ($tenant->handle ?? ''),
                    'primary_color' => $primaryColor,
                    'primary_color_hex' => (string) (data_get($primaryPalette, 500) ?? data_get($primaryPalette, '500') ?? '#3d5ccc'),
                    'logo_radius' => (string) data_get($themeOptions, 'logoRadius', data_get($schema, 'logoRadius.default', 'rounded-full')),
                    'font_family' => (string) data_get($themeOptions, 'fontFamily', data_get($schema, 'fontFamily.default', 'sarmady')),
                    'header_image' => $headerImage,
                    'header_image_url' => $headerImageUrl,
                    'header_image_position' => (int) data_get($themeOptions, 'headerImagePosition', 50),
                ],
                'goal' => [
                    'primary_action_type' => (string) data_get($tenant->meta, 'primary_action_type', ''),
                    'secondary_action_type' => (string) data_get($tenant->meta, 'secondary_action_type', ''),
                ],
                'catalog' => [
                    'enabled' => $enabledCatalogContentTypes,
                ],
                'orders' => [
                    'payment_active' => $onboarding->hasActivePayment($tenant),
                    'shipping_active' => $onboarding->hasActiveShipping($tenant),
                    'verification_done' => $onboarding->verificationDone($tenant),
                ],
            ],
            'industries' => collect($industryOptions)
                ->mapWithKeys(fn (array $option): array => [$option['slug'] => $option['label']])
                ->all(),
            'industry_options' => $industryOptions,
            'action_options' => $actionOptions,
            'social_networks' => collect(config('social-networks', []))
                ->map(fn (array $network): string => $network['label'])
                ->all(),
            'fonts' => data_get($schema, 'fontFamily.options', [
                'sarmady' => 'سرمدي',
                'ibmps' => 'IBM Plex',
                'effra' => 'Effra',
            ]),
            'color_options' => data_get($schema, 'primaryColor.options', []),
            'radius_options' => [],
            'catalog_options' => $catalogOptions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        return $this->handle($this->currentDashboardTenant($request), $onboarding);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): OnboardingResource
    {
        return new OnboardingResource($payload);
    }

    private function resolveHeaderImageUrl(Tenant $tenant, string $headerImage): string
    {
        if ($headerImage === '' || str_starts_with($headerImage, 'color:') || str_starts_with($headerImage, 'gradient:')) {
            return $headerImage;
        }

        if (str_starts_with($headerImage, 'http://') || str_starts_with($headerImage, 'https://')) {
            return $headerImage;
        }

        return (string) Storage::url($headerImage);
    }

    private function extractSocialUsername(string $network, string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $url) !== 1) {
            return ltrim($url, '@');
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return '';
        }

        $segment = basename($path);

        if ($network === 'youtube' || $network === 'tiktok') {
            $segment = ltrim($segment, '@');
        }

        return ltrim($segment, '@');
    }
}
