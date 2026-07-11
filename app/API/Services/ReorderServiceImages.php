<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Services\Concerns\ResolvesService;
use App\Models\Media;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders store project gallery images.
 */
class ReorderServiceImages
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesService;

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
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer'],
        ];
    }

    /**
     * @param  list<int>  $order
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function handle(Tenant $tenant, string $uuid, array $order): array
    {
        setCurrentTenant($tenant);

        $content = $this->findService($uuid);
        $validIds = $content->getMedia('service-media')->pluck('id')->all();

        $orderedIds = collect($order)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => in_array($id, $validIds, true))
            ->values()
            ->all();

        if ($orderedIds !== []) {
            Media::setNewOrder($orderedIds);
        }

        return [
            'images' => $content->fresh()->serviceImages(),
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
