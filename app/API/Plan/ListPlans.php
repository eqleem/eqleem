<?php

namespace App\API\Plan;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PlanCatalogResource;
use App\Models\Tenant;
use App\Support\PlanCatalog;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListPlans
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'billing_period' => ['sometimes', 'string', 'in:monthly,yearly'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $billingPeriod, PlanCatalog $catalog): array
    {
        $tenant->loadMissing('subscription.plan');

        return [
            'billing_period' => $billingPeriod,
            'current_plan_id' => $tenant->subscription?->plan_id,
            'plans' => $catalog->displayPlans($billingPeriod, $tenant->subscription?->plan_id),
            'faqs' => $catalog->subscriptionFaqs(),
            'app_name' => (string) config('app.name'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, PlanCatalog $catalog): array
    {
        $tenant = $this->currentDashboardTenant($request);
        $billingPeriod = (string) $request->validated('billing_period', 'monthly');

        return $this->handle($tenant, $billingPeriod, $catalog);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PlanCatalogResource
    {
        return new PlanCatalogResource($payload);
    }
}
