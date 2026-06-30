<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Mail\RegistrationLink;
use App\Models\User;

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

        $token = Str::random(64);

        DB::table('registration_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        $url = route('auth.register.verify', ['token' => $token, 'email' => $email]);

        Mail::to($email)->queue(new RegistrationLink($url));

        return true;
    }
}

