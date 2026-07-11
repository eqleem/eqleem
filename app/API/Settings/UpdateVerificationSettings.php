<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\VerificationSettingsResource;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Submits or updates tenant identity verification documents.
 */
class UpdateVerificationSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenant = request()->user()?->currentTenant;
        $hasExistingFile = $tenant instanceof Tenant
            && filled(data_get($tenant->meta, 'identity_file'));

        $fileRules = $hasExistingFile
            ? ['nullable', 'image', 'max:5024']
            : ['required', 'image', 'max:5024'];

        return [
            'identity_type' => ['required', 'string', Rule::in(['individual', 'llc', 'company', 'charity'])],
            'identity_number' => ['required', 'string', 'min:8', 'max:255'],
            'country' => ['nullable', 'string', Rule::in(array_keys(config('verification.countries', [])))],
            'file' => $fileRules,
        ];
    }

    /**
     * @param  array{identity_type: string, identity_number: string, country?: string|null, file?: UploadedFile|null}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        $tenant->meta->set('identity_type', $data['identity_type']);
        $tenant->meta->set('identity_number', $data['identity_number']);
        $tenant->meta->set('country', $data['country'] ?? 'SA');

        $file = $data['file'] ?? null;

        if ($file instanceof UploadedFile) {
            // Tenant has no hashId accessor; use uuid for media paths.
            $mediaKey = (string) ($tenant->uuid ?? $tenant->id);
            $path = $file->storePublicly('catalog-media/'.$mediaKey.'/identity', 'spaces');
            $tenant->meta->set('identity_file', $path);
        }

        $tenant->meta->set('is_confirmed', false);
        $tenant->meta->set('confirm_status', 'pending');
        $tenant->save();

        return GetVerificationSettings::make()->handle($tenant->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{identity_type: string, identity_number: string, country?: string|null, file?: UploadedFile|null} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): VerificationSettingsResource
    {
        return (new VerificationSettingsResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
