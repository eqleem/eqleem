<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Block;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\Onboarding;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 1: business profile (industry, name, bio, logo).
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
        return [
            'industry' => ['required', 'string', Rule::in(array_keys(config('industries', [])))],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'bio' => ['required', 'string', 'max:250'],
            'logo' => ['nullable', 'image', 'max:15024'],
        ];
    }

    /**
     * @param  array{industry: string, name: string, bio: string, logo?: UploadedFile|null}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $tenant->name = $data['name'];
        $tenant->meta->set('industry', $data['industry']);

        $logo = $data['logo'] ?? null;

        if ($logo instanceof UploadedFile) {
            $path = $logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
            app(TenantProfileService::class)->saveLogo($tenant, $path);
        } else {
            $tenant->save();
        }

        $headerBlock = Block::findSingleton('header');

        if ($headerBlock) {
            $headerBlock->update([
                'data' => array_merge($headerBlock->data ?? [], [
                    'bio' => (string) $data['bio'],
                ]),
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

        /** @var array{industry: string, name: string, bio: string, logo?: UploadedFile|null} $validated */
        $validated = $request->validated();

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
