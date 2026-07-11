<?php

namespace App\Http\Resources;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tenant
 */
class TenantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $planName = data_get($this->resource, 'subscription.plan.name');

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'handle' => $this->handle,
            'logo' => $this->logo,
            'url' => $this->url,
            'active' => (bool) $this->active,
            'plan' => filled($planName) ? __((string) $planName) : __('free'),
            'custom_domain' => $this->custom_domain,
            'custom_domain_status' => $this->custom_domain_status,
            'app_domain' => (string) config('app.domain'),
        ];
    }
}
