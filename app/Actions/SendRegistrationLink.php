<?php

namespace App\Actions;

use App\Mail\RegistrationLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SendRegistrationLink
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    public function handle(string $email): bool
    {
        $this->fill(['email' => $email]);
        $this->validateAttributes();

        $throttleKey = 'registration-link:'.strtolower($email);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "يرجى الانتظار {$seconds} ثانية قبل إعادة إرسال رابط الدخول.",
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $token = Str::random(64);
        $code = (string) random_int(100000, 999999);

        DB::table('registration_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => hash('sha256', $token),
                'code' => hash('sha256', $code),
                'created_at' => now(),
            ]
        );

        $url = route('auth.register.verify', ['token' => $token, 'email' => $email]);

        Mail::to($email)->queue(new RegistrationLink($url, $code));

        return true;
    }
}
