<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a content page (text, slug, and publish fields).
 */
class UpdatePage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPage;

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
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'editor_mode' => ['sometimes', 'nullable', 'string', Rule::in(['html', 'markdown'])],
            'slug' => ['required', 'string', 'max:255'],
            'published' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('published')) {
            $request->merge([
                'published' => filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    /**
     * @param  array{
     *     title: string,
     *     subtitle?: string|null,
     *     body?: string|null,
     *     editor_mode?: string,
     *     slug: string,
     *     published: bool
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $payload = $content->data ?? [];

        $payload['subtitle'] = (string) ($data['subtitle'] ?? '');
        $payload['body'] = (string) ($data['body'] ?? '');
        $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));

        $slug = $this->uniquePageSlug(
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

        return $content->fresh();
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     title: string,
         *     subtitle?: string|null,
         *     body?: string|null,
         *     editor_mode?: string,
         *     slug: string,
         *     published: bool
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): PageResource
    {
        return (new PageResource($content, [
            'slug_prefix' => $this->slugPrefix(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
