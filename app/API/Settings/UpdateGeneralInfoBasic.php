<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Settings\Concerns\BuildsGeneralInfoSettings;
use App\Http\Resources\GeneralInfoSettingsResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates general info basic fields (name, logo / brand mark).
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
            'brand_mark_type' => ['nullable', 'string', Rule::in(['image', 'emoji', 'icon', 'none'])],
            'brand_mark_value' => ['nullable', 'string', 'max:64'],
            'brand_mark_color' => ['nullable', 'string', 'max:20'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array{
     *     name: string,
     *     logo?: UploadedFile|null,
     *     brand_mark_type?: string|null,
     *     brand_mark_value?: string|null,
     *     brand_mark_color?: string|null,
     *     remove_logo?: bool
     * }  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        $tenant->name = $data['name'];
        $tenant->save();

        $profile = app(TenantProfileService::class);
        $logo = $data['logo'] ?? null;
        $markType = (string) ($data['brand_mark_type'] ?? '');

        if ($logo instanceof UploadedFile) {
            $path = $logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
            $profile->saveLogo($tenant, $path);
        } elseif ((bool) ($data['remove_logo'] ?? false) || $markType === 'none') {
            $profile->clearBrandMark($tenant);
        } elseif (in_array($markType, ['emoji', 'icon'], true)) {
            $profile->saveBrandMark($tenant, [
                'type' => $markType,
                'value' => (string) ($data['brand_mark_value'] ?? ''),
                'color' => (string) ($data['brand_mark_color'] ?? ''),
            ]);
        }

        return $this->generalInfoPayload($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo');
        }

        if ($request->boolean('remove_logo')) {
            $validated['remove_logo'] = true;
        }

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
