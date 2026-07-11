<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\AnalyticsSettingsResource;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns analytics integration settings for the current dashboard tenant.
 */
class GetAnalyticsSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $saved = Setting::forGroup('analytics');
        $providers = config('analytics.providers', []);
        $integrations = [];

        foreach (array_keys($providers) as $provider) {
            $row = $saved->get($provider);

            $integrations[$provider] = [
                'identifier' => (string) data_get($row, 'settings.identifier', ''),
                'active' => (bool) data_get($row, 'active', false),
            ];
        }

        return [
            'integrations' => $integrations,
            'providers' => $providers,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        return $this->handle($this->currentDashboardTenant($request));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): AnalyticsSettingsResource
    {
        return new AnalyticsSettingsResource($payload);
    }
}
