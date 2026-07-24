<?php

namespace App\Actions;

use App\Support\HashedLoginCode;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class VerifyRegistrationCode
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ];
    }

    /**
     * @return array{tenant: mixed, user: mixed}
     */
    public function handle(string $email, string $code): array
    {
        $this->fill(compact('email', 'code'));
        $this->validateAttributes();

        $record = DB::table('registration_tokens')
            ->where('email', $email)
            ->first();

        HashedLoginCode::assertValidRegistrationToken(
            $record,
            $code,
            fn () => HashedLoginCode::forget('registration_tokens', ['email' => $email]),
        );

        return VerifyRegistration::make()->complete($email);
    }
}
