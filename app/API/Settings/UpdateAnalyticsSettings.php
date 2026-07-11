<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\AnalyticsSettingsResource;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates analytics integration settings for the current dashboard tenant.
 */
class UpdateAnalyticsSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(config('analytics.providers', [])) as $provider) {
            $rules["integrations.{$provider}.identifier"] = [
                'nullable',
                'string',
                'max:100',
                "required_if:integrations.{$provider}.active,true",
            ];
            $rules["integrations.{$provider}.active"] = ['boolean'];
        }

        return $rules;
    }

    /**
     * @param  array{integrations?: array<string, array{identifier?: string|null, active?: bool}>}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        $integrations = $data['integrations'] ?? [];

        foreach ($integrations as $provider => $row) {
            if (! array_key_exists($provider, config('analytics.providers', []))) {
                continue;
            }

            $identifier = trim((string) data_get($row, 'identifier', ''));
            $active = (bool) data_get($row, 'active', false);
            $slug = Setting::groupSlug('analytics', $provider);

            if ($identifier === '') {
                Setting::query()
                    ->where('tenant_id', currentTenantId())
                    ->where('slug', $slug)
                    ->delete();

                continue;
            }

            Setting::saveForSlug($slug, ['identifier' => $identifier], $active);
        }

        return GetAnalyticsSettings::make()->handle($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{integrations?: array<string, array{identifier?: string|null, active?: bool}>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): AnalyticsSettingsResource
    {
        return (new AnalyticsSettingsResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
