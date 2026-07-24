<?php

namespace App\API\Courses\Concerns;

use App\Models\Content;
use App\Models\Media;
use App\Models\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesCourse
{
    protected function courseType(): string
    {
        return contentTypeModel('courses');
    }

    protected function findCourse(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->courseType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    /** @var Collection<int, array{id: string, label: string, selectable: bool}>|null */
    private ?Collection $cachedCategoryOptions = null;

    /**
     * @return Collection<int, array{id: string, label: string, selectable: bool}>
     */
    protected function categoryOptions(): Collection
    {
        if ($this->cachedCategoryOptions instanceof Collection) {
            return $this->cachedCategoryOptions;
        }

        $tree = Taxonomy::flatTree('course_category');
        $parentIds = $tree
            ->pluck('parent_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return $this->cachedCategoryOptions = $tree
            ->map(fn (Taxonomy $item): array => [
                'id' => (string) $item->id,
                'label' => str_repeat('— ', (int) ($item->depth ?? 0)).$item->name,
                'selectable' => ! $parentIds->has((int) $item->id),
            ]);
    }

    /**
     * @param  array<int, int|string>  $categoryIds
     * @return list<int>
     */
    protected function selectableCategoryIds(array $categoryIds): array
    {
        $selectableIds = $this->categoryOptions()
            ->where('selectable', true)
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        return collect($categoryIds)
            ->map(fn (mixed $id): string => (string) $id)
            ->intersect($selectableIds)
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();
    }

    protected function uniqueCourseSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'course';
        $counter = 1;

        while (
            Content::query()
                ->where('slug', $slug)
                ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugifyTitle(string $title): string
    {
        $slug = Str::slug($title);

        return $slug !== '' ? $slug : 'course';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/courses/';
    }

    /**
     * @return array<int, array{id: string, title: string, description: string, lessons: array<int, array<string, mixed>>}>
     */
    protected function normalizeChapters(Content $content, mixed $chapters): array
    {
        if (! is_array($chapters)) {
            return [];
        }

        $lessonFiles = collect($content->courseLessonFiles())->keyBy('lesson_id');

        return collect($chapters)
            ->filter(fn (mixed $chapter): bool => is_array($chapter))
            ->values()
            ->map(function (array $chapter) use ($lessonFiles): array {
                $chapterId = filled($chapter['id'] ?? null)
                    ? (string) $chapter['id']
                    : (string) Str::uuid();

                $lessons = collect($chapter['lessons'] ?? [])
                    ->filter(fn (mixed $lesson): bool => is_array($lesson))
                    ->values()
                    ->map(function (array $lesson) use ($lessonFiles): array {
                        $lessonId = filled($lesson['id'] ?? null)
                            ? (string) $lesson['id']
                            : (string) Str::uuid();

                        $normalized = $this->blankLesson();
                        $normalized['id'] = $lessonId;
                        $normalized['title'] = (string) ($lesson['title'] ?? '');
                        $normalized['description'] = (string) ($lesson['description'] ?? '');
                        $normalized['source'] = in_array($lesson['source'] ?? 'file', ['file', 'link'], true)
                            ? (string) $lesson['source']
                            : 'file';
                        $normalized['link'] = (string) ($lesson['link'] ?? '');

                        $media = $lessonFiles->get($lessonId);

                        if ($media) {
                            $normalized['source'] = 'file';
                            $normalized['media_id'] = $media['id'];
                            $normalized['file_name'] = $media['name'];
                            $normalized['file_url'] = $media['url'];
                        } elseif (isset($lesson['media_id'])) {
                            $normalized['media_id'] = (int) $lesson['media_id'];
                            $normalized['file_name'] = (string) ($lesson['file_name'] ?? '');
                            $normalized['file_url'] = (string) ($lesson['file_url'] ?? '');
                        }

                        return $normalized;
                    })
                    ->all();

                return [
                    'id' => $chapterId,
                    'title' => (string) ($chapter['title'] ?? ''),
                    'description' => (string) ($chapter['description'] ?? ''),
                    'lessons' => $lessons,
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $chapters
     * @return array<int, array<string, mixed>>
     */
    protected function serializeChapters(array $chapters): array
    {
        return collect($chapters)
            ->map(fn (array $chapter): array => [
                'id' => (string) ($chapter['id'] ?? Str::uuid()),
                'title' => trim((string) ($chapter['title'] ?? '')),
                'description' => trim((string) ($chapter['description'] ?? '')),
                'lessons' => collect($chapter['lessons'] ?? [])
                    ->map(fn (array $lesson): array => [
                        'id' => (string) ($lesson['id'] ?? Str::uuid()),
                        'title' => trim((string) ($lesson['title'] ?? '')),
                        'description' => trim((string) ($lesson['description'] ?? '')),
                        'source' => in_array($lesson['source'] ?? 'file', ['file', 'link'], true)
                            ? $lesson['source']
                            : 'file',
                        'link' => ($lesson['source'] ?? 'file') === 'link'
                            ? trim((string) ($lesson['link'] ?? ''))
                            : '',
                        'media_id' => filled($lesson['media_id'] ?? null) ? (int) $lesson['media_id'] : null,
                        'file_name' => (string) ($lesson['file_name'] ?? ''),
                        'file_url' => (string) ($lesson['file_url'] ?? ''),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function blankLesson(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'title' => '',
            'description' => '',
            'source' => 'file',
            'link' => '',
            'media_id' => null,
            'file_name' => '',
            'file_url' => '',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $serializedChapters
     */
    protected function syncLessonMedia(Content $content, array $serializedChapters): void
    {
        $lessonIds = collect($serializedChapters)
            ->flatMap(fn (array $chapter): array => collect($chapter['lessons'] ?? [])
                ->pluck('id')
                ->filter()
                ->map(fn (mixed $id): string => (string) $id)
                ->all())
            ->values()
            ->all();

        $content->getMedia('course-lesson-files')
            ->filter(function (Media $media) use ($lessonIds): bool {
                $lessonId = (string) $media->getCustomProperty('lesson_id');

                return $lessonId === '' || ! in_array($lessonId, $lessonIds, true);
            })
            ->each(fn (Media $media) => $media->delete());
    }

    protected function deleteLessonMedia(Content $content, int $mediaId): void
    {
        $media = $content->getMedia('course-lesson-files')->firstWhere('id', $mediaId);

        if ($media instanceof Media) {
            $media->delete();
        }
    }

    protected function deleteLessonMediaForLesson(Content $content, string $lessonId): void
    {
        $content->getMedia('course-lesson-files')
            ->filter(fn (Media $media): bool => (string) $media->getCustomProperty('lesson_id') === $lessonId)
            ->each(fn (Media $media) => $media->delete());
    }
}
