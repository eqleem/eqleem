<?php

namespace App\API\Settings;

use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Updates (or clears) the current tenant's custom domain.
 */
class UpdateTenantCustomDomain
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    public function handle(Tenant $tenant, ?string $customDomain): Tenant
    {
        if ($customDomain === null || $customDomain === '') {
            $tenant->custom_domain = null;
            $tenant->custom_domain_status = null;
        } else {
            $previousDomain = (string) ($tenant->custom_domain ?? '');

            $tenant->custom_domain = $customDomain;
            $tenant->custom_domain_status = $previousDomain === $customDomain && filled($tenant->custom_domain_status)
                ? (string) $tenant->custom_domain_status
                : 'pending';
        }

        $tenant->save();

        $tenant = $tenant->fresh(['subscription.plan']);
        setCurrentTenant($tenant);

        return $tenant;
    }

    public function asController(Request $request): Tenant
    {
        $tenant = $this->managedTenant($request);

        $normalized = $this->normalizeDomain((string) $request->input('custom_domain', ''));
        $customDomain = $normalized === '' ? null : $normalized;

        if ($customDomain !== null) {
            Validator::make(
                ['custom_domain' => $customDomain],
                [
                    'custom_domain' => [
                        'required',
                        'string',
                        'max:255',
                        'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
                    ],
                ],
                ['custom_domain.regex' => 'صيغة الدومين غير صحيحة.'],
            )->validate();

            if ($this->customDomainIsTaken($customDomain, $tenant)) {
                throw ValidationException::withMessages([
                    'custom_domain' => 'هذا الدومين مستخدم بالفعل.',
                ]);
            }
        }

        return $this->handle($tenant, $customDomain);
    }

    public function jsonResponse(Tenant $tenant): TenantResource
    {
        return (new TenantResource($tenant))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }

    private function managedTenant(Request $request): Tenant
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AccessDeniedHttpException;
        }

        $tenant = $user->currentTenant;

        if (! $tenant instanceof Tenant || ! $user->ownsTenant($tenant)) {
            throw new AccessDeniedHttpException;
        }

        return $tenant;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;

        return rtrim($domain, '/');
    }

    private function customDomainIsTaken(string $domain, Tenant $tenant): bool
    {
        return Tenant::query()
            ->whereKeyNot($tenant->id)
            ->where('custom_domain', $domain)
            ->exists();
    }
}
