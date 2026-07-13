<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PaymentOptionResource;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\PaymentMethodRegistry;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists payment options for the current dashboard tenant.
 */
class ListPaymentOptions
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(Tenant $tenant): Collection
    {
        setCurrentTenant($tenant);

        return app(PaymentMethodRegistry::class)->all()
            ->map(function ($method): array {
                $saved = Setting::paymentMethod($method->slug);
                $settings = collect($saved)->except('active')->all();

                return [
                    'slug' => $method->slug,
                    'name' => $method->name,
                    'description' => $method->description,
                    'icon' => $method->icon,
                    'icon_url' => asset($method->icon),
                    'available' => $method->available,
                    'active' => (bool) data_get($saved, 'active', false),
                    'settings' => $settings,
                    'order' => $method->order,
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function asController(ActionRequest $request): Collection
    {
        return $this->handle($this->currentDashboardTenant($request));
    }

    public function jsonResponse(Collection $options): AnonymousResourceCollection
    {
        return PaymentOptionResource::collection($options);
    }
}
