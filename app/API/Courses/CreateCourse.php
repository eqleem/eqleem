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
 * Creates a draft course (title only).
 */
class CreateCourse
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function handle(Tenant $tenant, string $title): Content
    {
        setCurrentTenant($tenant);

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $this->courseType(),
            'title' => $title,
            'slug' => $this->uniqueCourseSlug($this->slugifyTitle($title)),
            'status' => 'draft',
            'active' => true,
            'data' => [
                'level' => 'beginner',
                'course_type' => 'recorded',
                'hours' => 0,
                'chapters' => [],
            ],
        ]);
    }

    public function asController(ActionRequest $request): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{title: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, trim($validated['title']));
    }

    public function jsonResponse(Content $content): CourseResource
    {
        return (new CourseResource($content->loadMissing(['media']), [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'chapters' => $this->normalizeChapters($content, data_get($content->data, 'chapters', [])),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
