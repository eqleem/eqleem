<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientSocialAccount;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class ClientAuthService
{
    /**
     * @param  array{name?: string|null, email?: string|null, phone?: string|null, avatar?: string|null}  $profile
     */
    public function findOrCreateClient(string $email, array $profile = []): Client
    {
        $client = Client::withoutGlobalScope('tenantable')
            ->where('email', $email)
            ->first();

        if ($client) {
            return $client;
        }

        $name = $profile['name'] ?? explode('@', $email)[0];

        return Client::withoutGlobalScope('tenantable')->create([
            'name' => $name,
            'email' => $email,
            'phone' => $profile['phone'] ?? null,
            'email_verified_at' => now(),
            'meta' => filled($profile['avatar'] ?? null) ? ['avatar' => $profile['avatar']] : null,
        ]);
    }

    /**
     * @param  array{name?: string|null, email?: string|null, phone?: string|null, avatar?: string|null}  $profile
     */
    public function linkClientToTenant(Client $client, Tenant $tenant, array $profile = []): Client
    {
        $existingPivot = $client->tenants()
            ->where('tenants.id', $tenant->id)
            ->first()
            ?->pivot;

        $existingMeta = is_array($existingPivot?->meta) ? $existingPivot->meta : [];

        $meta = array_filter([
            'name' => $profile['name'] ?? $existingMeta['name'] ?? $client->name,
            'email' => $profile['email'] ?? $existingMeta['email'] ?? $client->email,
            'phone' => $profile['phone'] ?? $existingMeta['phone'] ?? $client->phone,
            'avatar' => $profile['avatar'] ?? $existingMeta['avatar'] ?? data_get($client->meta, 'avatar'),
        ], fn ($value) => filled($value));

        $client->tenants()->syncWithoutDetaching([
            $tenant->id => [
                'active' => true,
                'meta' => $meta,
            ],
        ]);

        if (! $client->tenant_id) {
            $client->update(['tenant_id' => $tenant->id]);
        }

        return $client->fresh();
    }

    /**
     * @param  array{name?: string|null, email?: string|null, phone?: string|null, avatar?: string|null}  $profile
     */
    public function authenticateForTenant(string $email, Tenant $tenant, array $profile = []): Client
    {
        $client = $this->findOrCreateClient($email, $profile);
        $client = $this->linkClientToTenant($client, $tenant, $profile);

        $guestSessionId = session()->getId();

        app(CartService::class)->stashGuestCartReference($tenant->id);

        Auth::guard('client')->login($client, remember: true);

        app(CartService::class)->mergeGuestCartInto($client, $tenant->id, $guestSessionId);

        return $client;
    }

    public function handleSocialAccount(Client $client, string $provider, SocialiteUser $socialUser): void
    {
        ClientSocialAccount::updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'client_id' => $client->id,
                'provider_token' => $socialUser->token ?? null,
                'provider_refresh_token' => $socialUser->refreshToken ?? null,
                'meta' => [
                    'id' => $socialUser->getId(),
                    'nickname' => $socialUser->getNickname(),
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'avatar' => $socialUser->getAvatar(),
                ],
            ],
        );
    }

    public function findClientBySocial(string $provider, string $providerId): ?Client
    {
        $account = ClientSocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        return $account?->client;
    }

    public function logout(): void
    {
        Auth::guard('client')->logout();
    }
}
