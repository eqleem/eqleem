<?php

namespace App\API\Tenants;

use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists tenants owned by the authenticated user (صفحاتي).
 */
class ListUserTenants
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

    /**
     * @return Collection<int, Tenant>
     */
    public function handle(User $user): Collection
    {
        return $user->tenants()
            ->with('subscription.plan')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function asController(ActionRequest $request): Collection
    {
        /** @var User $user */
        $user = $request->user();

        return $this->handle($user);
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     */
    public function jsonResponse(Collection $tenants): AnonymousResourceCollection
    {
        return TenantResource::collection($tenants);
    }
}
