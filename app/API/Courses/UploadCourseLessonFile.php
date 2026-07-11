<?php

namespace App\API\Courses;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Courses\Concerns\ResolvesCourse;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads a lesson file for a course chapter lesson.
 */
class UploadCourseLessonFile
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
        $maxFileSizeKb = (int) (config('media-library.max_file_size') / 1024);

        return [
            'file' => ['required', 'file', 'max:'.$maxFileSizeKb],
            'chapter_id' => ['required', 'string', 'max:64'],
            'lesson_id' => ['required', 'string', 'max:64'],
        ];
    }

    /**
     * @return array{
     *     chapter_id: string,
     *     lesson_id: string,
     *     media_id: int,
     *     file_name: string,
     *     file_url: string
     * }
     */
    public function handle(Tenant $tenant, string $uuid, UploadedFile $file, string $chapterId, string $lessonId): array
    {
        setCurrentTenant($tenant);

        $content = $this->findCourse($uuid);

        $this->deleteLessonMediaForLesson($content, $lessonId);

        $media = $content
            ->addMedia($file)
            ->usingFileName(md5($file->getClientOriginalName()).'.'.$file->getClientOriginalExtension())
            ->withCustomProperties([
                'chapter_id' => $chapterId,
                'lesson_id' => $lessonId,
            ])
            ->toMediaCollection('course-lesson-files');

        return [
            'chapter_id' => $chapterId,
            'lesson_id' => $lessonId,
            'media_id' => (int) $media->id,
            'file_name' => $media->file_name,
            'file_url' => $media->getUrl(),
        ];
    }

    /**
     * @return array{
     *     chapter_id: string,
     *     lesson_id: string,
     *     media_id: int,
     *     file_name: string,
     *     file_url: string
     * }
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var UploadedFile $file */
        $file = $request->file('file');

        /** @var array{chapter_id: string, lesson_id: string} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            $uuid,
            $file,
            $validated['chapter_id'],
            $validated['lesson_id'],
        );
    }

    /**
     * @param  array{
     *     chapter_id: string,
     *     lesson_id: string,
     *     media_id: int,
     *     file_name: string,
     *     file_url: string
     * }  $result
     * @return array{data: array<string, mixed>, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('تم رفع ملف الدرس بنجاح.'),
        ];
    }
}
