<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\ShippingMethodResource;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\ShippingMethodRegistry;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates shipping method settings while preserving active.
 */
class UpdateShippingMethodSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:120'],
            'domestic_price' => ['nullable', 'numeric', 'min:0'],
            'gulf_price' => ['nullable', 'numeric', 'min:0'],
            'international_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $slug, array $data): array
    {
        setCurrentTenant($tenant);

        $method = app(ShippingMethodRegistry::class)->find($slug);

        if (! $method) {
            throw new NotFoundHttpException;
        }

        $active = (bool) data_get(Setting::shippingMethod($slug), 'active', false);

        Setting::saveShippingMethod($slug, [
            'label' => trim((string) ($data['label'] ?? '')),
            'domestic_price' => isset($data['domestic_price']) && $data['domestic_price'] !== null && $data['domestic_price'] !== ''
                ? (float) $data['domestic_price']
                : null,
            'gulf_price' => isset($data['gulf_price']) && $data['gulf_price'] !== null && $data['gulf_price'] !== ''
                ? (float) $data['gulf_price']
                : null,
            'international_price' => isset($data['international_price']) && $data['international_price'] !== null && $data['international_price'] !== ''
                ? (float) $data['international_price']
                : null,
        ], $active);

        $fresh = Setting::shippingMethod($slug);

        return [
            'slug' => $method->slug,
            'name' => $method->name,
            'description' => $method->description,
            'icon' => $method->icon,
            'icon_url' => asset($method->icon),
            'active' => (bool) data_get($fresh, 'active', false),
            'settings' => collect($fresh)->except('active')->all(),
            'order' => $method->order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $slug): array
    {
        if (! config("shipping-methods.{$slug}")) {
            throw new NotFoundHttpException;
        }

        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $slug, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): ShippingMethodResource
    {
        return (new ShippingMethodResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
