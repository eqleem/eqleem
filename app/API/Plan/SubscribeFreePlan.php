<?php

namespace App\API\Plan;

use App\Actions\SubscribeTenantToPlan;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PlanCatalogResource;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\PlanCatalog;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class SubscribeFreePlan
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, PlanCatalog $catalog): array
    {
        $plan = Plan::query()
            ->where('slug', 'free')
            ->where('is_system', true)
            ->first();

        if (! $plan) {
            abort(422, 'تعذّر تفعيل الباقة المجانية.');
        }

        SubscribeTenantToPlan::run($tenant, $plan);

        $tenant = $tenant->fresh(['subscription.plan']);

        return [
            'billing_period' => 'monthly',
            'current_plan_id' => $tenant->subscription?->plan_id,
            'plans' => $catalog->displayPlans('monthly', $tenant->subscription?->plan_id),
            'faqs' => $catalog->subscriptionFaqs(),
            'app_name' => (string) config('app.name'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, PlanCatalog $catalog): array
    {
        return $this->handle($this->currentDashboardTenant($request), $catalog);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PlanCatalogResource
    {
        return (new PlanCatalogResource($payload))
            ->additional(['message' => 'تم تفعيل الباقة المجانية.']);
    }
}
