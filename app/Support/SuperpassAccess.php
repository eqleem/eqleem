<?php

namespace App\Support;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Validation\ValidationException;

class SuperpassAccess
{
    public static function assertCanAccess(User $user, string $field = 'data.email'): void
    {
        $panel = Filament::getPanel('superpass');

        if ($user instanceof FilamentUser && ! $user->canAccessPanel($panel)) {
            throw ValidationException::withMessages([
                $field => 'غير مصرح لهذا الحساب بالدخول إلى لوحة التحكم.',
            ]);
        }
    }
}
