<?php

namespace App\Actions;

use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Support\SocialiteUserMeta;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleSocialCallback
{
    use AsAction;

    public function handle(string $provider, SocialiteUser $socialUser): User
    {
        $email = $socialUser->getEmail();
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = $this->createUser($socialUser, $email);
        }

        SocialAccount::firstOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token ?? null,
                'provider_refresh_token' => $socialUser->refreshToken ?? null,
                'meta' => SocialiteUserMeta::from($socialUser),
            ]
        );

        $tenant = $this->ensureTenantForUser($user, $email, $socialUser->getName());

        if ($user->current_tenant_id !== $tenant->id) {
            $user->update(['current_tenant_id' => $tenant->id]);
        }

        return $user->fresh();
    }

    protected function createUser(SocialiteUser $socialUser, string $email): User
    {
        $nickname = $socialUser->getNickname() ?? explode('@', $email)[0];

        do {
            $username = $nickname.'-'.generateKey(7);
        } while (User::where('username', $username)->exists());

        $user = User::create([
            'name' => $socialUser->getName() ?? $username,
            'email' => $email,
            'image' => $socialUser->getAvatar(),
        ]);

        $user->update(['username' => $username]);

        return $user;
    }

    protected function ensureTenantForUser(User $user, string $email, ?string $name): Tenant
    {
        $tenant = Tenant::where('user_id', $user->id)->first();

        if ($tenant) {
            return $tenant;
        }

        $emailPrefix = explode('@', $email)[0];
        $tenantHandle = $user->username ?? $emailPrefix;

        while (Tenant::where('handle', $tenantHandle)->exists()) {
            $tenantHandle = $emailPrefix.'-'.generateKey(7);
        }

        return CreateTenant::run([
            'tenant_name' => $name
            ?? $emailPrefix,
            'tenant_handle' => $tenantHandle,
            'email' => $email,
            'user_id' => $user->id,
        ]);
    }
}
