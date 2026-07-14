<?php

namespace App\Actions;

use App\Mail\ClientLoginCode;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SendClientLoginCode
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'tenantId' => ['required', 'integer', 'exists:tenants,id'],
        ];
    }

    public function handle(string $email, int $tenantId): void
    {
        $this->fill(compact('email', 'tenantId'));
        $this->validateAttributes();

        $throttleKey = 'client-login-code:'.$tenantId.':'.strtolower($email);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "يرجى الانتظار {$seconds} ثانية قبل إعادة إرسال رابط الدخول.",
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $code = (string) random_int(100000, 999999);
        $tenant = Tenant::query()->findOrFail($tenantId);
        $normalizedEmail = strtolower($email);

        DB::table('client_login_codes')->updateOrInsert(
            [
                'email' => $normalizedEmail,
                'tenant_id' => $tenant->id,
            ],
            [
                'code' => hash('sha256', $code),
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ],
        );

        $loginUrl = URL::temporarySignedRoute(
            'tenant.client.auth.email',
            now()->addMinutes(10),
            [
                'tenant' => $tenant->handle,
                'email' => $normalizedEmail,
                'code' => $code,
            ],
        );

        Mail::to($email)->queue(new ClientLoginCode($code, $tenant, $loginUrl));
    }
}
