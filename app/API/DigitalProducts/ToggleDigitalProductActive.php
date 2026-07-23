<?php

namespace App\API\DigitalProducts;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalProducts\Concerns\ResolvesDigitalProduct;
use App\Http\Resources\DigitalProductListResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Toggles active (published) state for a digital product.
 */
class ToggleDigitalProductActive
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesDigitalProduct;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('active')) {
            $request->merge([
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    public function handle(Tenant $tenant, string $uuid, bool $active): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findDigitalProduct($uuid);
        $content->update([
            'active' => $active,
            'status' => $active ? 'published' : 'draft',
            'published_at' => $active
                ? ($content->published_at ?? now())
                : null,
        ]);

        return $content->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, (bool) $validated['active']);
    }

    public function jsonResponse(Content $content): DigitalProductListResource
    {
        return (new DigitalProductListResource($content))
            ->additional([
                'message' => $content->active ? 'تم تفعيل المنتج.' : 'تم تعطيل المنتج.',
            ]);
    }
}
