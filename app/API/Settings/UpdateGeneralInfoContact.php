<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Settings\Concerns\BuildsGeneralInfoSettings;
use App\Http\Resources\GeneralInfoSettingsResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates general info contact fields.
 */
class UpdateGeneralInfoContact
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
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @param  array{phone?: string|null, whatsapp?: string|null, email?: string|null, country?: string|null, city?: string|null}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        app(TenantProfileService::class)->saveContact($tenant, [
            'phone' => (string) ($data['phone'] ?? ''),
            'whatsapp' => (string) ($data['whatsapp'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'country' => (string) ($data['country'] ?? ''),
            'city' => (string) ($data['city'] ?? ''),
        ]);

        return $this->generalInfoPayload($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{phone?: string|null, whatsapp?: string|null, email?: string|null, country?: string|null, city?: string|null} $validated */
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
