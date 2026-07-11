<?php

namespace App\API\Store;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Store\Concerns\ResolvesStoreProduct;
use App\Http\Resources\StoreProductResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a store product's fields (title, body, pricing, categories, publish state).
 */
class UpdateStoreProduct
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesStoreProduct;

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
            'body' => ['nullable', 'string'],
            'editor_mode' => ['sometimes', 'nullable', 'string', Rule::in(['html', 'markdown'])],
            'slug' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'category_ids' => ['sometimes', 'nullable', 'array'],
            'category_ids.*' => [
                'numeric',
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'store_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
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
     * @param  array{
     *     title: string,
     *     body?: string|null,
     *     editor_mode?: string,
     *     slug: string,
     *     price?: float|string|null,
     *     compare_price?: float|string|null,
     *     weight?: float|string|null,
     *     category_ids?: list<int>|null,
     *     published: bool
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findStoreProduct($uuid);
        $payload = $content->data ?? [];

        $payload['body'] = (string) ($data['body'] ?? '');
        $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));
        $payload['price'] = filled($data['price'] ?? null) ? money_minor($data['price']) : 0;
        $payload['compare_price'] = filled($data['compare_price'] ?? null) ? money_minor($data['compare_price']) : null;
        $payload['weight'] = filled($data['weight'] ?? null) ? (string) $data['weight'] : null;

        $slug = $this->uniqueStoreSlug(
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
            'store_category',
            $this->selectableCategoryIds($data['category_ids'] ?? []),
        );

        return $content->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     title: string,
         *     body?: string|null,
         *     editor_mode?: string,
         *     slug: string,
         *     price?: float|string|null,
         *     compare_price?: float|string|null,
         *     weight?: float|string|null,
         *     category_ids?: list<int>|null,
         *     published: bool
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): StoreProductResource
    {
        return (new StoreProductResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
