<?php

namespace App\Http\Resources;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array{
 *     user: User,
 *     tenant: Tenant|null,
 *     can_access_dashboard: bool,
 *     can_manage_tenant: bool,
 *     app_name: string,
 *     home_url: string,
 *     logout_url: string
 * } $resource
 */
class DashboardContextResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource['user']),
            'tenant' => $this->resource['tenant']
                ? new TenantResource($this->resource['tenant'])
                : null,
            'permissions' => [
                'can_access_dashboard' => $this->resource['can_access_dashboard'],
                'can_manage_tenant' => $this->resource['can_manage_tenant'],
            ],
            'app' => [
                'name' => $this->resource['app_name'],
                'home_url' => $this->resource['home_url'],
                'logout_url' => $this->resource['logout_url'],
            ],
        ];
    }
}
