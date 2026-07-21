<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

        if (! $record || blank($record->code) || ! hash_equals($record->code, hash('sha256', $code))) {
            throw ValidationException::withMessages([
                'code' => 'كود التحقق غير صحيح. يرجى المحاولة مرة أخرى.',
            ]);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('registration_tokens')->where('email', $email)->delete();

            throw ValidationException::withMessages([
                'code' => 'انتهت صلاحية كود التحقق. يرجى طلب كود جديد.',
            ]);
        }

        return VerifyRegistration::make()->complete($email);
    }
}
