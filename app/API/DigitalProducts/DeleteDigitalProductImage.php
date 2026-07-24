<?php

namespace App\API\DigitalProducts;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalProducts\Concerns\ResolvesDigitalProduct;
use App\Models\Media;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a gallery image from a store project.
 */
class DeleteDigitalProductImage
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
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function handle(Tenant $tenant, string $uuid, int $mediaId): array
    {
        setCurrentTenant($tenant);

        $content = $this->findDigitalProduct($uuid);
        $media = $content->getMedia('digital-product-media')->firstWhere('id', $mediaId);

        if (! $media instanceof Media) {
            throw new NotFoundHttpException;
        }

        $media->delete();

        return [
            'images' => $content->reloadMediaCollection('digital-product-media')->digitalProductImages(),
        ];
    }

    /**
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function asController(ActionRequest $request, string $uuid, int $mediaId): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid, $mediaId);
    }

    /**
     * @param  array{images: list<array{id: int, url: string}>}  $result
     * @return array{data: array{images: list<array{id: int, url: string}>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
