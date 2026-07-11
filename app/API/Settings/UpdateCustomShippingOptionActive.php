<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\CustomShippingOptionResource;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Toggles a custom shipping option active flag.
 */
class UpdateCustomShippingOptionActive
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
    public function handle(Tenant $tenant, string $id, bool $active): array
    {
        setCurrentTenant($tenant);

        $items = Setting::customShippingOptions();
        $payload = null;

        foreach ($items as $index => $item) {
            if (($item['id'] ?? null) !== $id) {
                continue;
            }

            $items[$index]['active'] = $active;
            $payload = $items[$index];
            break;
        }

        if ($payload === null) {
            throw new NotFoundHttpException;
        }

        Setting::saveCustomShippingOptions($items);

        return [
            'id' => (string) ($payload['id'] ?? $id),
            'name' => (string) ($payload['name'] ?? ''),
            'price' => (float) ($payload['price'] ?? 0),
            'country' => (string) ($payload['country'] ?? '*'),
            'all_cities' => (bool) ($payload['all_cities'] ?? false),
            'city_ids' => array_values((array) ($payload['city_ids'] ?? [])),
            'active' => (bool) ($payload['active'] ?? false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $id): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, (bool) $validated['active']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): CustomShippingOptionResource
    {
        return (new CustomShippingOptionResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
