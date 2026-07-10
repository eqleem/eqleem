<?php

namespace App\API\Dashboard;

use App\Http\Resources\DashboardContextResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Resolves the authenticated user's dashboard context from current_tenant_id:
 * user profile, current tenant (when owned), and access permissions.
 *
 * @see https://www.laravelactions.com/2.x/examples/get-user-profile.html
 */
class GetDashboardContext
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

    /**
     * @return array{
     *     user: User,
     *     tenant: Tenant|null,
     *     can_access_dashboard: bool,
     *     can_manage_tenant: bool,
     *     app_name: string,
     *     home_url: string,
     *     logout_url: string
     * }
     */
    public function handle(User $user): array
    {
        $user->loadMissing([
            'currentTenant.subscription.plan',
        ]);

        $tenant = $user->currentTenant;
        $canManageTenant = $tenant instanceof Tenant && $user->ownsTenant($tenant);
        $canAccessDashboard = $canManageTenant && $user->canAccessDashboard($tenant);

        if ($canManageTenant) {
            setCurrentTenant($tenant);
        }

        return [
            'user' => $user,
            'tenant' => $canManageTenant ? $tenant : null,
            'can_access_dashboard' => $canAccessDashboard,
            'can_manage_tenant' => $canManageTenant,
            'app_name' => (string) config('app.name'),
            'home_url' => route('home'),
            'logout_url' => route('auth.logout'),
        ];
    }

    /**
     * @return array{
     *     user: User,
     *     tenant: Tenant|null,
     *     can_access_dashboard: bool,
     *     can_manage_tenant: bool,
     *     app_name: string,
     *     home_url: string,
     *     logout_url: string
     * }
     */
    public function asController(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        return $this->handle($user);
    }

    /**
     * @param  array{
     *     user: User,
     *     tenant: Tenant|null,
     *     can_access_dashboard: bool,
     *     can_manage_tenant: bool,
     *     app_name: string,
     *     home_url: string,
     *     logout_url: string
     * }  $context
     */
    public function jsonResponse(array $context): DashboardContextResource
    {
        return new DashboardContextResource($context);
    }
}
