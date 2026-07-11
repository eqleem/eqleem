<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Settings\Concerns\BuildsGeneralInfoSettings;
use App\Http\Resources\GeneralInfoSettingsResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates general info basic fields (name, logo).
 */
class UpdateGeneralInfoBasic
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'logo' => ['nullable', 'image', 'max:15024'],
        ];
    }

    /**
     * @param  array{name: string, logo?: UploadedFile|null}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        $tenant->name = $data['name'];

        $logo = $data['logo'] ?? null;

        if ($logo instanceof UploadedFile) {
            $path = $logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
            app(TenantProfileService::class)->saveLogo($tenant, $path);
        } else {
            $tenant->save();
        }

        return $this->generalInfoPayload($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{name: string, logo?: UploadedFile|null} $validated */
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
