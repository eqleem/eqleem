<?php

namespace App\API\Settings;

use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Updates the current tenant's free subdomain handle.
 */
class UpdateTenantHandle
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

    public function handle(Tenant $tenant, string $handle): Tenant
    {
        $tenant->handle = $handle;
        $tenant->save();

        $tenant = $tenant->fresh(['subscription.plan']);
        setCurrentTenant($tenant);

        return $tenant;
    }

    public function asController(Request $request): Tenant
    {
        $tenant = $this->managedTenant($request);

        /** @var array{handle: string} $validated */
        $validated = $request->validate([
            'handle' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'alpha_dash:ascii',
                Rule::unique('tenants', 'handle')->ignore($tenant->id),
            ],
        ]);

        return $this->handle($tenant, $validated['handle']);
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
}
