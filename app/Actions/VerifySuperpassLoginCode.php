<?php

namespace App\Actions;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
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

        $record = DB::table('superpass_login_codes')
            ->where('email', $normalizedEmail)
            ->first();

        if (! $record || ! hash_equals($record->code, hash('sha256', $code))) {
            throw ValidationException::withMessages([
                'data.code' => 'كود التحقق غير صحيح. يرجى المحاولة مرة أخرى.',
            ]);
        }

        if (now()->greaterThan($record->expires_at)) {
            DB::table('superpass_login_codes')
                ->where('email', $normalizedEmail)
                ->delete();

            throw ValidationException::withMessages([
                'data.code' => 'انتهت صلاحية كود التحقق. يرجى طلب كود جديد.',
            ]);
        }

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

        DB::table('superpass_login_codes')
            ->where('email', $normalizedEmail)
            ->delete();

        return $user;
    }
}
