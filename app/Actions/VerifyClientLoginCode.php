<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Services\ClientAuthService;
use App\Support\HashedLoginCode;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class VerifyClientLoginCode
{
    use AsAction, WithAttributes;

    public function __construct(protected ClientAuthService $clientAuth) {}

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

        $normalizedEmail = strtolower($email);
        $where = ['email' => $normalizedEmail, 'tenant_id' => $tenantId];

        $record = DB::table('client_login_codes')->where($where)->first();

        HashedLoginCode::assertValid(
            $record,
            $code,
            'code',
            __('OTP code is not correct. Please try again.'),
            'code',
            'انتهت صلاحية كود التحقق. يرجى طلب كود جديد.',
            fn () => HashedLoginCode::forget('client_login_codes', $where),
        );

        $tenant = Tenant::query()->findOrFail($tenantId);
        $name = explode('@', $email)[0];

        $this->clientAuth->authenticateForTenant($email, $tenant, [
            'name' => $name,
            'email' => $email,
        ]);

        HashedLoginCode::forget('client_login_codes', $where);
    }
}
