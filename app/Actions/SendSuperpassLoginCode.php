<?php

namespace App\Actions;

use App\Mail\SuperpassLoginCode;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
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
        $throttleKey = 'superpass-login-code:'.$normalizedEmail;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'data.email' => "يرجى الانتظار {$seconds} ثانية قبل إعادة إرسال كود الدخول.",
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        /** @var User|null $user */
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.email' => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني.',
            ]);
        }

        $panel = Filament::getPanel('superpass');

        if ($user instanceof FilamentUser && ! $user->canAccessPanel($panel)) {
            throw ValidationException::withMessages([
                'data.email' => 'غير مصرح لهذا الحساب بالدخول إلى لوحة التحكم.',
            ]);
        }

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
