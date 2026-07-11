<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Settings\Concerns\BuildsGeneralInfoSettings;
use App\Http\Resources\GeneralInfoSettingsResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Adds a social link to general info settings.
 */
class AddGeneralInfoSocialLink
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use BuildsGeneralInfoSettings;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'network' => ['required', 'string', Rule::in(array_keys(config('social-networks', [])))],
            'url' => ['required', 'url', 'max:500'],
        ];
    }

    /**
     * @param  array{network: string, url: string}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        app(TenantProfileService::class)->addSocialLink($tenant, $data['network'], $data['url']);

        return $this->generalInfoPayload($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{network: string, url: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): GeneralInfoSettingsResource
    {
        return (new GeneralInfoSettingsResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
