<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class VerifyRegistration
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'token' => 'required|string|min:64|max:64',
        ];
    }

    public function handle(string $email, string $token): array
    {
        $this->fill(['email' => $email, 'token' => $token]);
        $this->validateAttributes();

        $record = DB::table('registration_tokens')
            ->where('email', $email)
            ->first();

        if (! $record) {
            throw new \Exception('رابط التسجيل غير صالح أو منتهي الصلاحية.');
        }

        if (! hash_equals($record->token, hash('sha256', $token))) {
            throw new \Exception('رابط التسجيل غير صالح.');
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('registration_tokens')->where('email', $email)->delete();
            throw new \Exception('رابط التسجيل منتهي الصلاحية. يرجى طلب رابط جديد.');
        }

        return $this->complete($email);
    }

    /**
     * Complete login or registration after the email has already been verified.
     *
     * @return array{tenant: ?Tenant, user: ?User}
     */
    public function complete(string $email): array
    {
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            DB::table('registration_tokens')->where('email', $email)->delete();

            $tenant = $existingUser->currentTenant
                ?? Tenant::query()->where('user_id', $existingUser->id)->first();

            if ($tenant && (int) $existingUser->current_tenant_id !== (int) $tenant->id) {
                $existingUser->update(['current_tenant_id' => $tenant->id]);
            }

            return ['tenant' => $tenant, 'user' => $existingUser->fresh()];
        }

        $password = Str::random(16);

        $emailPrefix = explode('@', $email)[0];
        do {
            $username = $emailPrefix.'-'.generateKey(7);
        } while (User::where('username', $username)->exists());

        $user = CreateUser::run([
            'name' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $user->update(['username' => $username]);

        $tenant = null;

        if ($user) {
            $tenantHandle = $username;

            $tenant = CreateTenant::run([
                'tenant_name' => $emailPrefix,
                'tenant_handle' => $tenantHandle,
                'email' => $email,
                'user_id' => $user->id,
            ]);
        }

        if ($tenant) {
            $user->update([
                'current_tenant_id' => $tenant->id,
            ]);
        }

        DB::table('registration_tokens')->where('email', $email)->delete();

        return ['tenant' => $tenant, 'user' => $user];
    }
}
