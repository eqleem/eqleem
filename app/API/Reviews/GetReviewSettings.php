<?php

namespace App\API\Reviews;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns reviews section customize settings.
 */
class GetReviewSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array{section_title: string, per_page: int}
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $settings = Setting::reviewSettings();

        return [
            'section_title' => (string) ($settings['section_title'] ?? ''),
            'per_page' => (int) ($settings['per_page'] ?? 12),
        ];
    }

    /**
     * @return array{section_title: string, per_page: int}
     */
    public function asController(ActionRequest $request): array
    {
        return $this->handle($this->currentDashboardTenant($request));
    }

    /**
     * @param  array{section_title: string, per_page: int}  $settings
     * @return array{data: array{section_title: string, per_page: int}}
     */
    public function jsonResponse(array $settings): array
    {
        return [
            'data' => $settings,
        ];
    }
}
