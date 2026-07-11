<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PaymentOptionResource;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\PaymentMethodRegistry;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Toggles a payment option active flag while preserving settings.
 */
class UpdatePaymentOptionActive
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

        $method = app(PaymentMethodRegistry::class)->find($slug);

        if (! $method) {
            throw new NotFoundHttpException;
        }

        $saved = Setting::paymentMethod($slug);
        $settings = collect($saved)->except('active')->all();

        Setting::savePaymentMethod($slug, $settings, $active);

        $fresh = Setting::paymentMethod($slug);

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
    public function jsonResponse(array $payload): PaymentOptionResource
    {
        return (new PaymentOptionResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
