<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Support\ContentTypeRegistry;
use App\Support\Onboarding;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 4: enabled sellable catalog content types.
 */
class SaveOnboardingCatalog
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sellableSlugs = app(ContentTypeRegistry::class)->configured()
            ->filter(fn ($type): bool => $type->sellable)
            ->pluck('slug')
            ->all();

        return [
            'enabled' => ['required', 'array', 'min:1'],
            'enabled.*' => ['required', 'string', Rule::in($sellableSlugs)],
        ];
    }

    /**
     * @param  array{enabled: list<string>}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $config = is_array($tenant->config) ? $tenant->config : [];
        $config['enabled_content_types'] = array_values(array_unique($data['enabled']));
        $tenant->config = $config;
        $tenant->save();

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{enabled: list<string>} $validated */
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
