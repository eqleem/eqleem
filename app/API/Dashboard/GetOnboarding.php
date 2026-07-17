<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\ContentTypeRegistry;
use App\Support\Onboarding;
use App\Support\TenantThemeOptions;
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

        return [
            'percentage' => $progress['percentage'],
            'completed_steps' => $progress['completed'],
            'total_steps' => $progress['total'],
            'current_step' => $progress['current_step'],
            'completed' => $progress['percentage'] >= 100,
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
                    'social_links' => $profile->socialLinks($tenant)->values()->all(),
                ],
                'identity' => [
                    'theme_id' => $themeId > 0 ? $themeId : null,
                    'primary_color' => (string) data_get($themeOptions, 'primaryColor', data_get($schema, 'primaryColor.default', 'blue')),
                    'logo_radius' => (string) data_get($themeOptions, 'logoRadius', data_get($schema, 'logoRadius.default', 'rounded-full')),
                    'font_family' => (string) data_get($themeOptions, 'fontFamily', data_get($schema, 'fontFamily.default', 'sarmady')),
                ],
                'catalog' => [
                    'enabled' => $enabledContentTypes,
                ],
                'orders' => [
                    'payment_active' => $onboarding->hasActivePayment($tenant),
                    'shipping_active' => $onboarding->hasActiveShipping($tenant),
                    'verification_done' => $onboarding->verificationDone($tenant),
                ],
            ],
            'industries' => config('industries', []),
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
}
