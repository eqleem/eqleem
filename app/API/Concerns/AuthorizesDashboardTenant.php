<?php

namespace App\API\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Lorisleiva\Actions\ActionRequest;

trait AuthorizesDashboardTenant
{
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
        $user = $request->user();

        if (! $user instanceof User) {
            return false;
        }

        $tenant = $user->currentTenant;

        if (! $tenant instanceof Tenant || ! $user->canAccessDashboard($tenant)) {
            return false;
        }

        setCurrentTenant($tenant);

        return true;
    }

    protected function currentDashboardTenant(ActionRequest $request): Tenant
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Tenant $tenant */
        $tenant = $user->currentTenant;

        setCurrentTenant($tenant);

        return $tenant;
    }

    /**
     * @return array<string, mixed>
     */
    protected function listQueryRules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderRules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer'],
        ];
    }
}
