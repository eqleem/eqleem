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
 * Toggles a shipping method active flag while preserving settings.
 */
class UpdateShippingMethodActive
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $slug, bool $active): array
    {
        setCurrentTenant($tenant);

        $method = app(ShippingMethodRegistry::class)->find($slug);

        if (! $method) {
            throw new NotFoundHttpException;
        }

        $saved = Setting::shippingMethod($slug);
        $settings = collect($saved)->except('active')->all();

        Setting::saveShippingMethod($slug, $settings, $active);

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
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $slug, (bool) $validated['active']);
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
