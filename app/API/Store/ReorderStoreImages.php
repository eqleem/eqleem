<?php

namespace App\API\Store;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Store\Concerns\ResolvesStoreProduct;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders store product gallery images.
 */
class ReorderStoreImages
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesStoreProduct;

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
        return $this->orderRules();
    }

    /**
     * @param  list<int>  $order
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function handle(Tenant $tenant, string $uuid, array $order): array
    {
        setCurrentTenant($tenant);

        $content = $this->findStoreProduct($uuid);
        $content->reorderMediaCollection(Content::MEDIA_STORE, $order);

        return [
            'images' => $content->reloadMediaCollection(Content::MEDIA_STORE)->storeImages(),
        ];
    }

    /**
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<int>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated['order']);
    }

    /**
     * @param  array{images: list<array{id: int, url: string}>}  $result
     * @return array{data: array{images: list<array{id: int, url: string}>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
