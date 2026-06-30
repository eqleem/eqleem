<?php

namespace App\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Actions\CreateUser;
use App\Actions\CreateTenant;
use App\Models\User;
use App\Models\Tenant;

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

        if (!$record) {
            throw new \Exception('رابط التسجيل غير صالح أو منتهي الصلاحية.');
        }

        if (!hash_equals($record->token, hash('sha256', $token))) {
            throw new \Exception('رابط التسجيل غير صالح.');
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('registration_tokens')->where('email', $email)->delete();
            throw new \Exception('رابط التسجيل منتهي الصلاحية. يرجى طلب رابط جديد.');
        }

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            DB::table('registration_tokens')->where('email', $email)->delete();

            // Return existing user and tenant to log them in
            $existingUser->load('tenant');
            return ['tenant' => $existingUser->tenant, 'user' => $existingUser];
        }

        // Generate random password
        $password = Str::random(16);
        
        // Generate username from email prefix (ensure uniqueness)
        $emailPrefix = explode('@', $email)[0];
        do {
            $username = $emailPrefix . '-' . generateKey(7);
        } while (User::where('username', $username)->exists());
 
        // Create user
        $user = CreateUser::run([
            'name' => $username, // Use email prefix as name
            'email' => $email,
            'password' => $password,
        ]);
        
        // Update username after creation (since CreateUser doesn't handle it)
        $user->update(['username' => $username]);

        if ($user) {
            // Generate tenant handle
            $tenantHandle = $username;
            
            // Create tenant
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

        // Delete the token
        DB::table('registration_tokens')->where('email', $email)->delete();

        return ['tenant' => $tenant ?? null, 'user' => $user ?? null];
    }
}

