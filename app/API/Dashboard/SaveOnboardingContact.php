<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\Onboarding;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 2: contact details and social links.
 */
class SaveOnboardingContact
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $networks = array_keys(config('social-networks', []));

        return [
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'city' => ['required', 'string', 'max:100'],
            'social_links' => ['required', 'array', 'min:1'],
            'social_links.*.network' => ['required', 'string', Rule::in($networks)],
            'social_links.*.url' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array{
     *     phone: string,
     *     email: string,
     *     whatsapp: string,
     *     country: string,
     *     city: string,
     *     social_links: list<array{network: string, url: string}>
     * }  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $profile = app(TenantProfileService::class);

        $profile->saveContact($tenant, [
            'phone' => $data['phone'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'],
            'country' => strtoupper($data['country']),
            'city' => $data['city'],
        ]);

        $links = [];
        $order = 1;

        foreach ($data['social_links'] as $link) {
            $links[] = [
                'id' => (string) str()->uuid(),
                'network' => $link['network'],
                'url' => $link['url'],
                'sort_order' => $order++,
            ];
        }

        $tenant->meta->set('social_links', $links);
        $tenant->save();

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     phone: string,
         *     email: string,
         *     whatsapp: string,
         *     country: string,
         *     city: string,
         *     social_links: list<array{network: string, url: string}>
         * } $validated
         */
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
