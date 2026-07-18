<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\Onboarding;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 1: business profile (industry, name, bio, brand mark).
 */
class SaveOnboardingBusiness
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $partial = request()->boolean('partial');
        $required = $partial ? 'sometimes' : 'required';

        return [
            'partial' => ['sometimes', 'boolean'],
            'industry' => [$required, 'string', Rule::in(array_keys(config('industries', [])))],
            'name' => [$required, 'string', 'min:2', 'max:255'],
            'bio' => [$required, 'string', 'max:250'],
            'logo' => ['nullable', 'image', 'max:15024'],
            'brand_mark_type' => ['nullable', 'string', Rule::in(['image', 'emoji', 'icon', 'none'])],
            'brand_mark_value' => ['nullable', 'string', 'max:64'],
            'brand_mark_color' => ['nullable', 'string', 'max:20'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $profile = app(TenantProfileService::class);

        if (array_key_exists('name', $data) && filled($data['name'])) {
            $tenant->name = (string) $data['name'];
        }

        if (array_key_exists('industry', $data) && filled($data['industry'])) {
            $tenant->meta->set('industry', $data['industry']);
        }

        $tenant->save();

        if (array_key_exists('bio', $data) && filled($data['bio'])) {
            $profile->saveBio($tenant, (string) $data['bio']);
        }

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

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
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

        return $this->handle($tenant, $validated, $onboarding);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): OnboardingResource
    {
        return (new OnboardingResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
