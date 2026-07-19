<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Support\Onboarding;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 4: primary and secondary page action types.
 */
class SaveOnboardingGoal
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $partial = request()->boolean('partial');
        $actionKeys = array_keys(config('onboarding-actions', []));

        return [
            'partial' => ['sometimes', 'boolean'],
            'primary_action_type' => [
                $partial ? 'sometimes' : 'required',
                'string',
                Rule::in($actionKeys),
            ],
            'secondary_action_type' => [
                'nullable',
                'string',
                Rule::in($actionKeys),
                'different:primary_action_type',
            ],
        ];
    }

    /**
     * @param  array{primary_action_type?: string|null, secondary_action_type?: string|null}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $partial = (bool) ($data['partial'] ?? false);

        if (array_key_exists('primary_action_type', $data) && filled($data['primary_action_type'])) {
            $tenant->meta->set('primary_action_type', (string) $data['primary_action_type']);
        }

        if (array_key_exists('secondary_action_type', $data)) {
            if (filled($data['secondary_action_type'] ?? null)) {
                $tenant->meta->set('secondary_action_type', (string) $data['secondary_action_type']);
            } elseif (! $partial) {
                // Full saves may clear the secondary button. Partial autosaves often
                // send null when the field is untouched — ignore those so a slower
                // in-flight primary-only request cannot wipe a later secondary save.
                $tenant->meta->set('secondary_action_type', null);
            }
        }

        $tenant->save();

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{partial?: bool, primary_action_type?: string|null, secondary_action_type?: string|null} $validated */
        $validated = $request->validated();
        $validated['partial'] = $request->boolean('partial');

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
