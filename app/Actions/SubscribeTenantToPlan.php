<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;
use LucasDotVin\Soulbscription\Models\Subscription;

class SubscribeTenantToPlan
{
    use AsAction;

    public function handle(Tenant $tenant, Plan $plan): Subscription
    {
        $subscription = $tenant->subscription
            ? $tenant->switchTo($plan)
            : $tenant->subscribeTo($plan);

        $this->refreshCurrentTenantContext($tenant);

        return $subscription;
    }

    public function subscribeToFreePlan(Tenant $tenant): ?Subscription
    {
        $plan = Plan::query()
            ->where('slug', 'free')
            ->where('is_system', true)
            ->first();

        if (! $plan || $tenant->subscription) {
            return $tenant->subscription;
        }

        $subscription = $tenant->subscribeTo($plan);

        $this->refreshCurrentTenantContext($tenant);

        return $subscription;
    }

    protected function refreshCurrentTenantContext(Tenant $tenant): void
    {
        if (currentTenant()?->is($tenant)) {
            setCurrentTenant($tenant->fresh(['subscription.plan.features']));
        }
    }
}
