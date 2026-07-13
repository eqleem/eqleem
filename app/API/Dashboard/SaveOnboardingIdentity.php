<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\SavePageThemeOptions;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Support\Onboarding;
use App\Support\TenantThemeOptions;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 3: brand identity (color, radius, font).
 */
class SaveOnboardingIdentity
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'primary_color' => ['required', 'string', 'max:40'],
            'logo_radius' => ['required', 'string', Rule::in([
                'rounded-full',
                'rounded-2xl',
                'rounded-lg',
                'rounded-md',
                'rounded-none',
                'full',
            ])],
            'font_family' => ['required', 'string', 'max:60'],
        ];
    }

    /**
     * @param  array{primary_color: string, logo_radius: string, font_family: string}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $tenant->loadMissing('theme');
        $themeId = (int) ($tenant->theme_id ?? 0);

        abort_unless($themeId > 0, 422, __('No active theme found.'));

        $themeSlug = $tenant->theme?->slug ?? 'default';
        $schema = app(TenantThemeOptions::class)->schemaForTheme($themeSlug);
        $saved = $tenant->themeSettingsFor($themeId);

        $radius = $data['logo_radius'];

        if ($radius === 'full') {
            $radius = 'rounded-full';
        }

        $options = [
            'primaryColor' => $data['primary_color'],
            'logoRadius' => $radius,
            'fontFamily' => $data['font_family'],
            'bgColor' => data_get($saved, 'bgColor', data_get($schema, 'bgColor.default', 'gray-300')),
            'headerImage' => data_get($saved, 'headerImage', data_get($schema, 'headerImage.default', '')),
        ];

        SavePageThemeOptions::make()->handle($tenant, $themeId, $options, []);

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{primary_color: string, logo_radius: string, font_family: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated, $onboarding);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): OnboardingResource
    {
        return (new OnboardingResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
