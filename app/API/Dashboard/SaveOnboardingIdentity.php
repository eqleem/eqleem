<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\SavePageThemeOptions;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Support\Onboarding;
use App\Support\TenantThemeOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 3: handle, brand color, and optional header image.
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
        $partial = request()->boolean('partial');
        $tenantId = request()->user()?->current_tenant_id;

        return [
            'partial' => ['sometimes', 'boolean'],
            'handle' => [
                'sometimes',
                'string',
                'min:2',
                'max:100',
                'alpha_dash:ascii',
                Rule::unique('tenants', 'handle')->ignore($tenantId),
            ],
            'primary_color' => [$partial ? 'sometimes' : 'required', 'string', 'max:40'],
            'logo_radius' => ['nullable', 'string', Rule::in([
                'rounded-full',
                'rounded-2xl',
                'rounded-lg',
                'rounded-md',
                'rounded-none',
                'full',
            ])],
            'font_family' => [$partial ? 'nullable' : 'sometimes', 'nullable', 'string', 'max:60'],
            'header_image' => ['nullable', 'string', 'max:2000'],
            'header_image_position' => ['nullable', 'integer', 'min:0', 'max:100'],
            'header_image_file' => ['nullable', 'image', 'max:15024'],
            'clear_header_image' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        if (array_key_exists('handle', $data) && filled($data['handle'])) {
            $tenant->handle = (string) $data['handle'];
            $tenant->save();
        }

        $tenant->loadMissing('theme');
        $themeId = (int) ($tenant->theme_id ?? 0);

        abort_unless($themeId > 0, 422, __('No active theme found.'));

        $themeSlug = $tenant->theme?->slug ?? 'default';
        $schema = app(TenantThemeOptions::class)->schemaForTheme($themeSlug);
        $saved = $tenant->themeSettingsFor($themeId);

        $radius = $data['logo_radius']
            ?? data_get($saved, 'logoRadius')
            ?? data_get($schema, 'logoRadius.default', 'rounded-full');

        if ($radius === 'full') {
            $radius = 'rounded-full';
        }

        $options = [
            'primaryColor' => $data['primary_color']
                ?? data_get($saved, 'primaryColor', data_get($schema, 'primaryColor.default', 'blue')),
            'logoRadius' => $radius,
            'fontFamily' => $data['font_family']
                ?? data_get($saved, 'fontFamily', data_get($schema, 'fontFamily.default', 'sarmady')),
            'bgColor' => data_get($saved, 'bgColor', data_get($schema, 'bgColor.default', 'gray-300')),
            'headerImage' => data_get($saved, 'headerImage', data_get($schema, 'headerImage.default', '')),
            'headerImagePosition' => (int) ($data['header_image_position']
                ?? data_get($saved, 'headerImagePosition', 50)),
        ];

        $uploads = [];

        if ((bool) ($data['clear_header_image'] ?? false)) {
            $options['headerImage'] = '__clear__';
        } elseif (($data['header_image_file'] ?? null) instanceof UploadedFile) {
            $uploads['headerImage'] = $data['header_image_file'];
        } elseif (array_key_exists('header_image', $data) && filled($data['header_image'])) {
            $options['headerImage'] = (string) $data['header_image'];
        }

        SavePageThemeOptions::make()->handle($tenant, $themeId, $options, $uploads);

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        if ($request->hasFile('header_image_file')) {
            $validated['header_image_file'] = $request->file('header_image_file');
        }

        if ($request->boolean('clear_header_image')) {
            $validated['clear_header_image'] = true;
        }

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
