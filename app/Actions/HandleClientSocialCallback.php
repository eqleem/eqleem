<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Services\ClientAuthService;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleClientSocialCallback
{
    use AsAction;

    public function handle(string $provider, SocialiteUser $socialUser, Tenant $tenant): void
    {
        if (! in_array($provider, ['google', 'github'], true)) {
            throw ValidationException::withMessages([
                'social' => 'مزود تسجيل الدخول غير مدعوم.',
            ]);
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            throw ValidationException::withMessages([
                'social' => 'تعذر الحصول على البريد الإلكتروني من مزود تسجيل الدخول.',
            ]);
        }

        $authService = app(ClientAuthService::class);
        $client = $authService->findClientBySocial($provider, $socialUser->getId());

        if (! $client) {
            $client = $authService->findOrCreateClient($email, [
                'name' => $socialUser->getName() ?? explode('@', $email)[0],
                'email' => $email,
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        $authService->handleSocialAccount($client, $provider, $socialUser);

        $authService->authenticateForTenant($email, $tenant, [
            'name' => $socialUser->getName() ?? $client->name,
            'email' => $email,
            'avatar' => $socialUser->getAvatar(),
        ]);
    }
}
