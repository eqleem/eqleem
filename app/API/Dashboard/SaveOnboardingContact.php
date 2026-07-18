<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\Onboarding;
use App\Support\SocialNetworkUrl;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves onboarding step 2: contact details and optional social link.
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
        $partial = request()->boolean('partial');
        $required = $partial ? 'sometimes' : 'required';
        $networks = array_keys(config('social-networks', []));

        return [
            'partial' => ['sometimes', 'boolean'],
            'phone' => [$required, 'string', 'max:30'],
            'email' => [$required, 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'whatsapp_same_as_phone' => ['sometimes', 'boolean'],
            'country' => ['nullable', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'city' => ['nullable', 'string', 'max:100'],
            'social_links' => ['nullable', 'array', 'max:1'],
            'social_links.*.network' => ['required_with:social_links', 'string', Rule::in($networks)],
            'social_links.*.url' => ['nullable', 'string', 'max:500'],
            'social_links.*.username' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data, Onboarding $onboarding): array
    {
        $profile = app(TenantProfileService::class);
        $existing = $profile->contact($tenant);
        $sameAsPhone = (bool) ($data['whatsapp_same_as_phone'] ?? false);

        $phone = array_key_exists('phone', $data)
            ? (string) $data['phone']
            : (string) ($existing['phone'] ?? '');

        if ($sameAsPhone) {
            $whatsapp = $phone;
        } elseif (array_key_exists('whatsapp', $data)) {
            $whatsapp = (string) ($data['whatsapp'] ?? '');
        } else {
            $whatsapp = (string) ($existing['whatsapp'] ?? '');
        }

        $profile->saveContact($tenant, [
            'phone' => $phone,
            'email' => array_key_exists('email', $data)
                ? (string) $data['email']
                : (string) ($existing['email'] ?? ''),
            'whatsapp' => $whatsapp,
            'country' => array_key_exists('country', $data) && filled($data['country'])
                ? strtoupper((string) $data['country'])
                : (string) ($existing['country'] ?? 'SA'),
            'city' => array_key_exists('city', $data)
                ? (string) ($data['city'] ?? '')
                : (string) ($existing['city'] ?? ''),
        ]);

        if (array_key_exists('social_links', $data)) {
            $links = [];
            $order = 1;

            foreach ($data['social_links'] ?? [] as $link) {
                $network = (string) ($link['network'] ?? '');
                $username = trim((string) ($link['username'] ?? ''));
                $url = trim((string) ($link['url'] ?? ''));

                if ($username !== '') {
                    $url = SocialNetworkUrl::resolve($network, $username);
                } elseif ($url !== '') {
                    $url = SocialNetworkUrl::resolve($network, $url);
                }

                if ($network === '' || $url === '') {
                    continue;
                }

                $links[] = [
                    'id' => (string) str()->uuid(),
                    'network' => $network,
                    'url' => $url,
                    'sort_order' => $order++,
                ];
            }

            $tenant->meta->set('social_links', $links);
            $tenant->save();
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
