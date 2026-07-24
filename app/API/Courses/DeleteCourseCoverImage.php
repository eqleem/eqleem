<?php

namespace App\API\Courses;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Courses\Concerns\ResolvesCourse;
use App\Models\Media;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes the course cover image.
 */
class DeleteCourseCoverImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesCourse;

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

        $content = $this->findCourse($uuid);
        $media = $content->getMedia('course-media')->firstWhere('id', $mediaId);

        if (! $media instanceof Media) {
            throw new NotFoundHttpException;
        }

        $media->delete();

        return [
            'images' => $content->reloadMediaCollection('course-media')->courseImages(),
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
