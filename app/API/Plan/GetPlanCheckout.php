<?php

namespace App\API\Plan;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PlanCheckoutResource;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPlanCheckout
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, Plan $plan): array
    {
        abort_unless($plan->is_system && $plan->active && ! $plan->isFree(), Response::HTTP_NOT_FOUND);

        $publishableKey = (string) config('services.moyasar.publishable_key');

        abort_if(blank($publishableKey), Response::HTTP_SERVICE_UNAVAILABLE, 'مفتاح Moyasar غير مُعرّف.');

        return [
            'plan' => [
                'id' => $plan->id,
                'title' => __((string) $plan->name),
                'price' => $plan->price,
                'price_formatted' => $plan->formattedPrice(),
                'interval_label' => $plan->billingLabel(),
            ],
            'checkout' => [
                'amount' => $plan->price,
                'currency' => money_currency(),
                'description' => 'اشتراك '.$plan->label.' — '.$plan->billingLabel(),
                'publishable_api_key' => $publishableKey,
                'callback_url' => route('dashboard.payments.moyasar.callback'),
                'methods' => ['creditcard'],
                'supported_networks' => ['mada', 'visa', 'mastercard'],
                'metadata' => [
                    'plan_id' => $plan->id,
                    'tenant_id' => $tenant->id,
                    'source' => 'dashboard',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Plan $plan): array
    {
        return $this->handle($this->currentDashboardTenant($request), $plan);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PlanCheckoutResource
    {
        return new PlanCheckoutResource($payload);
    }
}
