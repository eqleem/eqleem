<?php

namespace App\Actions;

use App\Models\User;
use App\Support\HashedLoginCode;
use App\Support\SuperpassAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class VerifySuperpassLoginCode
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ];
    }

    public function handle(string $email, string $code): User
    {
        $this->fill(compact('email', 'code'));
        $this->validateAttributes();

        $normalizedEmail = strtolower(trim($email));
        $where = ['email' => $normalizedEmail];

        $record = DB::table('superpass_login_codes')->where($where)->first();

        HashedLoginCode::assertValid(
            $record,
            $code,
            'data.code',
            'كود التحقق غير صحيح. يرجى المحاولة مرة أخرى.',
            'data.code',
            'انتهت صلاحية كود التحقق. يرجى طلب كود جديد.',
            fn () => HashedLoginCode::forget('superpass_login_codes', $where),
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

        HashedLoginCode::forget('superpass_login_codes', $where);

        return $user;
    }
}
