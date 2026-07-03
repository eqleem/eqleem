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
        if ($tenant->subscription) {
            return $tenant->switchTo($plan);
        }

        return $tenant->subscribeTo($plan);
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

        return $tenant->subscribeTo($plan);
    }
}
