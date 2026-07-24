<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HashedLoginCode
{
    /**
     * @param  callable(): void  $forget
     */
    public static function assertValid(
        ?object $record,
        string $code,
        string $invalidField,
        string $invalidMessage,
        string $expiredField,
        string $expiredMessage,
        callable $forget,
    ): void {
        $matches = $record
            && hash_equals((string) $record->code, hash('sha256', $code));

        if (! $matches) {
            throw ValidationException::withMessages([
                $invalidField => $invalidMessage,
            ]);
        }

        if (now()->greaterThan($record->expires_at)) {
            $forget();

            throw ValidationException::withMessages([
                $expiredField => $expiredMessage,
            ]);
        }
    }

    /**
     * Registration tokens expire relative to created_at (60 minutes), not expires_at.
     *
     * @param  callable(): void  $forget
     */
    public static function assertValidRegistrationToken(
        ?object $record,
        string $code,
        callable $forget,
    ): void {
        if (! $record || blank($record->code) || ! hash_equals($record->code, hash('sha256', $code))) {
            throw ValidationException::withMessages([
                'code' => 'كود التحقق غير صحيح. يرجى المحاولة مرة أخرى.',
            ]);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            $forget();

            throw ValidationException::withMessages([
                'code' => 'انتهت صلاحية كود التحقق. يرجى طلب كود جديد.',
            ]);
        }
    }

    public static function forget(string $table, array $where): void
    {
        DB::table($table)->where($where)->delete();
    }
}
