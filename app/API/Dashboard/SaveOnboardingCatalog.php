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
 * Saves onboarding catalog step: enabled sellable content types.
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

        $partial = request()->boolean('partial');

        return [
            'partial' => ['sometimes', 'boolean'],
            'enabled' => [$partial ? 'sometimes' : 'required', 'array', $partial ? 'nullable' : 'min:1'],
            'enabled.*' => ['required', 'string', Rule::in($sellableSlugs)],
        ];
    }

    /**
     * @param  array{enabled?: list<string>}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        if (! array_key_exists('enabled', $data)) {
            return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
        }

        $config = is_array($tenant->config) ? $tenant->config : [];
        $sellableSlugs = app(ContentTypeRegistry::class)->configured()
            ->filter(fn ($type): bool => $type->sellable)
            ->pluck('slug');
        $existing = collect($config['enabled_content_types'] ?? [])
            ->filter(fn (mixed $slug): bool => is_string($slug))
            ->reject(fn (string $slug): bool => $sellableSlugs->contains($slug));

        $config['enabled_content_types'] = $existing
            ->merge($data['enabled'] ?? [])
            ->unique()
            ->values()
            ->all();
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
