<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginCodeThrottle
{
    public static function hitOrFail(string $key, string $field, string $actionLabel = 'رابط الدخول'): void
    {
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                $field => "يرجى الانتظار {$seconds} ثانية قبل إعادة إرسال {$actionLabel}.",
            ]);
        }

        RateLimiter::hit($key, 60);
    }
}
