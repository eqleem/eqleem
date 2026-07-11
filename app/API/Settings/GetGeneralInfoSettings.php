<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Settings\Concerns\BuildsGeneralInfoSettings;
use App\Http\Resources\GeneralInfoSettingsResource;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns general info settings for the current dashboard tenant.
 */
class GetGeneralInfoSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use BuildsGeneralInfoSettings;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        return $this->generalInfoPayload($tenant);
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
    public function jsonResponse(array $payload): GeneralInfoSettingsResource
    {
        return new GeneralInfoSettingsResource($payload);
    }
}
