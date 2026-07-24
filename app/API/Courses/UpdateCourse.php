<?php

namespace App\API\Courses;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Courses\Concerns\ResolvesCourse;
use App\Http\Resources\CourseResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a course's fields (title, body, pricing, curriculum, categories, publish state).
 */
class UpdateCourse
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
            'subtitle' => ['nullable', 'string', 'max:300'],
            'body' => ['nullable', 'string'],
            'editor_mode' => ['sometimes', 'nullable', 'string', Rule::in(['html', 'markdown'])],
            'slug' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'hours' => ['nullable', 'numeric', 'min:0'],
            'level' => ['required', Rule::in(array_keys(Content::courseLevelOptions()))],
            'course_type' => ['required', Rule::in(array_keys(Content::courseTypeOptions()))],
            'category_ids' => ['sometimes', 'nullable', 'array'],
            'category_ids.*' => [
                'numeric',
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'course_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
            'published' => ['required', 'boolean'],
            'chapters' => ['sometimes', 'array'],
            'chapters.*.id' => ['nullable', 'string', 'max:64'],
            'chapters.*.title' => ['nullable', 'string', 'max:255'],
            'chapters.*.description' => ['nullable', 'string', 'max:1000'],
            'chapters.*.lessons' => ['sometimes', 'array'],
            'chapters.*.lessons.*.id' => ['nullable', 'string', 'max:64'],
            'chapters.*.lessons.*.title' => ['nullable', 'string', 'max:255'],
            'chapters.*.lessons.*.description' => ['nullable', 'string', 'max:1000'],
            'chapters.*.lessons.*.source' => ['required_with:chapters.*.lessons', Rule::in(['file', 'link'])],
            'chapters.*.lessons.*.link' => ['nullable', 'string', 'max:2000'],
            'chapters.*.lessons.*.media_id' => ['nullable', 'integer'],
            'chapters.*.lessons.*.file_name' => ['nullable', 'string', 'max:255'],
            'chapters.*.lessons.*.file_url' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('published')) {
            $request->merge([
                'published' => filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        if ($request->exists('category_ids') && is_array($request->input('category_ids'))) {
            $request->merge([
                'category_ids' => collect($request->input('category_ids'))
                    ->map(fn (mixed $id): int => (int) $id)
                    ->filter(fn (int $id): bool => $id > 0)
                    ->values()
                    ->all(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findCourse($uuid);
        $payload = $content->data ?? [];

        $payload['subtitle'] = (string) ($data['subtitle'] ?? '');
        $payload['body'] = (string) ($data['body'] ?? '');
        $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));
        $payload['price'] = filled($data['price'] ?? null) ? money_minor($data['price']) : 0;
        $payload['compare_price'] = filled($data['compare_price'] ?? null) ? money_minor($data['compare_price']) : null;
        $payload['hours'] = filled($data['hours'] ?? null) ? (float) $data['hours'] : 0;
        $payload['level'] = (string) ($data['level'] ?? 'beginner');
        $payload['course_type'] = (string) ($data['course_type'] ?? 'recorded');

        if (array_key_exists('chapters', $data)) {
            $normalized = $this->normalizeChapters($content, $data['chapters'] ?? []);
            $serialized = $this->serializeChapters($normalized);
            $payload['chapters'] = $serialized;
            $this->syncLessonMedia($content, $serialized);
        }

        $slug = $this->uniqueCourseSlug(
            filled($data['slug']) ? (string) $data['slug'] : Str::slug($data['title']),
            (int) $content->id,
        );

        $published = (bool) $data['published'];

        $content->update([
            'title' => $data['title'],
            'slug' => $slug,
            'status' => $published ? 'published' : 'draft',
            'data' => $payload,
            'published_at' => $published
                ? ($content->published_at ?? now())
                : null,
        ]);

        $content->syncTaxonomiesOfType(
            'course_category',
            $this->selectableCategoryIds($data['category_ids'] ?? []),
        );

        return $content->fresh(['media', 'taxonomies']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): CourseResource
    {
        return (new CourseResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'chapters' => $this->normalizeChapters($content, data_get($content->data, 'chapters', [])),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
