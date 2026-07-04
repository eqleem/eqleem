<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Services\ClientAuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class VerifyClientLoginCode
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'tenantId' => ['required', 'integer', 'exists:tenants,id'],
        ];
    }

    public function handle(string $email, string $code, int $tenantId): void
    {
        $this->fill(compact('email', 'code', 'tenantId'));
        $this->validateAttributes();

        $record = DB::table('client_login_codes')
            ->where('email', strtolower($email))
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $record || ! hash_equals($record->code, hash('sha256', $code))) {
            throw ValidationException::withMessages([
                'code' => __('OTP code is not correct. Please try again.'),
            ]);
        }

        if (now()->greaterThan($record->expires_at)) {
            DB::table('client_login_codes')
                ->where('email', strtolower($email))
                ->where('tenant_id', $tenantId)
                ->delete();

            throw ValidationException::withMessages([
                'code' => 'انتهت صلاحية كود التحقق. يرجى طلب كود جديد.',
            ]);
        }

        $tenant = Tenant::query()->findOrFail($tenantId);
        $name = explode('@', $email)[0];

        app(ClientAuthService::class)->authenticateForTenant($email, $tenant, [
            'name' => $name,
            'email' => $email,
        ]);

        DB::table('client_login_codes')
            ->where('email', strtolower($email))
            ->where('tenant_id', $tenantId)
            ->delete();
    }
}
