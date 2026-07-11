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
 * Deletes a lesson file from a course.
 */
class DeleteCourseLessonFile
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
     * @return array{media_id: int, deleted: bool}
     */
    public function handle(Tenant $tenant, string $uuid, int $mediaId): array
    {
        setCurrentTenant($tenant);

        $content = $this->findCourse($uuid);
        $media = $content->getMedia('course-lesson-files')->firstWhere('id', $mediaId);

        if (! $media instanceof Media) {
            throw new NotFoundHttpException;
        }

        $media->delete();

        return [
            'media_id' => $mediaId,
            'deleted' => true,
        ];
    }

    /**
     * @return array{media_id: int, deleted: bool}
     */
    public function asController(ActionRequest $request, string $uuid, int $mediaId): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid, $mediaId);
    }

    /**
     * @param  array{media_id: int, deleted: bool}  $result
     * @return array{data: array{media_id: int, deleted: bool}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
