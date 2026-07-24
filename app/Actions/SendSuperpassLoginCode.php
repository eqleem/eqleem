<?php

namespace App\Actions;

use App\Mail\SuperpassLoginCode;
use App\Models\User;
use App\Support\LoginCodeThrottle;
use App\Support\SuperpassAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SendSuperpassLoginCode
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function handle(string $email): void
    {
        $this->fill(compact('email'));
        $this->validateAttributes();

        $normalizedEmail = strtolower(trim($email));

        LoginCodeThrottle::hitOrFail(
            'superpass-login-code:'.$normalizedEmail,
            'data.email',
            'كود الدخول',
        );

        /** @var User|null $user */
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.email' => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني.',
            ]);
        }

        SuperpassAccess::assertCanAccess($user);

        $code = (string) random_int(100000, 999999);

        DB::table('superpass_login_codes')->updateOrInsert(
            ['email' => $normalizedEmail],
            [
                'code' => hash('sha256', $code),
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ],
        );

        Mail::to($user->email)->queue(new SuperpassLoginCode($code, $user->name));
    }
}
