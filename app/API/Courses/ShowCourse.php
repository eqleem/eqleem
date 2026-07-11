<?php

namespace App\API\Courses;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Courses\Concerns\ResolvesCourse;
use App\Http\Resources\CourseResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a course for editing.
 */
class ShowCourse
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesCourse;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findCourse($uuid);

        return $content->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): CourseResource
    {
        return new CourseResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'chapters' => $this->normalizeChapters($content, data_get($content->data, 'chapters', [])),
        ]);
    }
}
