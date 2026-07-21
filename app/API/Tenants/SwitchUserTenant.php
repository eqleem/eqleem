<?php

namespace App\API\Tenants;

use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Switches the authenticated user's current_tenant_id to an owned tenant.
 */
class SwitchUserTenant
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:60,1',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() instanceof User;
    }

    public function handle(User $user, int $tenantId): Tenant
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant instanceof Tenant) {
            throw new NotFoundHttpException(__('الصفحة غير موجودة.'));
        }

        if (! $user->ownsTenant($tenant)) {
            throw new AccessDeniedHttpException(__('لا يمكنك إدارة هذه الصفحة.'));
        }

        if (! $tenant->active) {
            throw new AccessDeniedHttpException(__('هذه الصفحة غير نشطة.'));
        }

        $user->update([
            'current_tenant_id' => $tenant->id,
        ]);

        setCurrentTenant($tenant);

        return $tenant->fresh()->loadMissing('subscription.plan');
    }

    public function asController(ActionRequest $request, int $tenant): Tenant
    {
        /** @var User $user */
        $user = $request->user();

        return $this->handle($user, $tenant);
    }

    public function jsonResponse(Tenant $tenant): TenantResource
    {
        return (new TenantResource($tenant))
            ->additional([
                'message' => __('تم التبديل إلى الصفحة.'),
            ]);
    }
}
