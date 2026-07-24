<?php

namespace App\Actions;

use App\Mail\RegistrationLink;
use App\Support\LoginCodeThrottle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

        LoginCodeThrottle::hitOrFail(
            'registration-link:'.strtolower($email),
            'email',
        );

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
