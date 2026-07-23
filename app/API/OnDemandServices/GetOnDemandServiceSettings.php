<?php

namespace App\API\OnDemandServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns on-demand services section customize settings.
 */
class GetOnDemandServiceSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array{section_title: string, section_description: string}
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $settings = Setting::onDemandServiceSettings();

        return [
            'section_title' => (string) ($settings['section_title'] ?? ''),
            'section_description' => (string) ($settings['section_description'] ?? ''),
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public function asController(ActionRequest $request): array
    {
        return $this->handle($this->currentDashboardTenant($request));
    }

    /**
     * @param  array{section_title: string, section_description: string}  $settings
     * @return array{data: array{section_title: string, section_description: string}}
     */
    public function jsonResponse(array $settings): array
    {
        return [
            'data' => $settings,
        ];
    }
}
