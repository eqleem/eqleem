<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\VerificationSettingsResource;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns verification settings for the current dashboard tenant.
 */
class GetVerificationSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $identityFile = data_get($tenant->meta, 'identity_file');

        return [
            'identity_type' => (string) (data_get($tenant->meta, 'identity_type') ?: 'individual'),
            'identity_number' => data_get($tenant->meta, 'identity_number'),
            'country' => (string) (data_get($tenant->meta, 'country') ?: 'SA'),
            'identity_file' => $identityFile,
            'identity_file_url' => filled($identityFile) ? Storage::url((string) $identityFile) : null,
            'is_confirmed' => (bool) data_get($tenant->meta, 'is_confirmed'),
            'confirm_status' => data_get($tenant->meta, 'confirm_status'),
            'types' => [
                'individual' => __('Individual'),
                'llc' => __('LLC'),
                'company' => __('Company'),
                'charity' => __('Charity'),
            ],
            'countries' => config('verification.countries', []),
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
    public function jsonResponse(array $payload): VerificationSettingsResource
    {
        return new VerificationSettingsResource($payload);
    }
}
