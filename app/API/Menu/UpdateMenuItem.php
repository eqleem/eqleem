<?php

namespace App\API\Menu;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Menu\Concerns\ResolvesMenuItem;
use App\Http\Resources\MenuItemResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a menu item's fields (title, pricing, meal options, categories, publish state).
 */
class UpdateMenuItem
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesMenuItem;

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
            'slug' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'meal_options' => ['sometimes', 'nullable', 'array'],
            'meal_options.*.id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'meal_options.*.name' => ['nullable', 'string', 'max:255'],
            'meal_options.*.type' => ['required', 'string', Rule::in(['single', 'multiple'])],
            'meal_options.*.required' => ['boolean'],
            'meal_options.*.choices' => ['nullable', 'array'],
            'meal_options.*.choices.*.id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'meal_options.*.choices.*.name' => ['nullable', 'string', 'max:255'],
            'meal_options.*.choices.*.price' => ['nullable', 'numeric', 'min:0'],
            'category_ids' => ['sometimes', 'nullable', 'array'],
            'category_ids.*' => [
                'numeric',
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'menu_category');

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

        if ($request->exists('meal_options') && is_array($request->input('meal_options'))) {
            $request->merge([
                'meal_options' => collect($request->input('meal_options'))
                    ->map(function (mixed $group): array {
                        if (! is_array($group)) {
                            return [];
                        }

                        return [
                            ...$group,
                            'required' => filter_var($group['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'choices' => collect($group['choices'] ?? [])
                                ->filter(fn (mixed $choice): bool => is_array($choice))
                                ->values()
                                ->all(),
                        ];
                    })
                    ->filter(fn (array $group): bool => $group !== [])
                    ->values()
                    ->all(),
            ]);
        }
    }

    /**
     * @param  array{
     *     title: string,
     *     slug: string,
     *     price?: float|string|null,
     *     compare_price?: float|string|null,
     *     meal_options?: list<array<string, mixed>>|null,
     *     category_ids?: list<int>|null,
     *     published: bool
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findMenuItem($uuid);
        $payload = $content->data ?? [];

        $payload['price'] = filled($data['price'] ?? null) ? money_minor($data['price']) : 0;
        $payload['compare_price'] = filled($data['compare_price'] ?? null) ? money_minor($data['compare_price']) : null;
        $payload['meal_options'] = $this->mealOptionsForStorage($data['meal_options'] ?? []);

        $slug = $this->uniqueMenuSlug(
            filled($data['slug']) ? (string) $data['slug'] : Str::slug($data['title']),
            (int) $content->id,
        );

        $active = (bool) $data['published'];

        $content->update([
            'title' => $data['title'],
            'slug' => $slug,
            'active' => $active,
            'status' => $active ? 'published' : 'draft',
            'data' => $payload,
            'published_at' => $active
                ? ($content->published_at ?? now())
                : null,
        ]);

        $content->syncTaxonomiesOfType(
            'menu_category',
            $this->selectableCategoryIds($data['category_ids'] ?? []),
        );

        return $content->fresh(['media', 'taxonomies']);
    }

    /**
     * @param  list<array<string, mixed>>  $mealOptions
     * @return list<array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     required: bool,
     *     choices: list<array{id: string, name: string, price: int}>
     * }>
     */
    protected function mealOptionsForStorage(array $mealOptions): array
    {
        return collect($mealOptions)
            ->filter(fn (mixed $group): bool => is_array($group))
            ->map(function (array $group): array {
                $choices = collect($group['choices'] ?? [])
                    ->filter(fn (mixed $choice): bool => is_array($choice))
                    ->filter(fn (array $choice): bool => filled($choice['name'] ?? null))
                    ->map(fn (array $choice): array => [
                        'id' => (string) ($choice['id'] ?? Str::uuid()),
                        'name' => trim((string) ($choice['name'] ?? '')),
                        'price' => filled($choice['price'] ?? null) ? money_minor($choice['price']) : 0,
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => (string) ($group['id'] ?? Str::uuid()),
                    'name' => trim((string) ($group['name'] ?? '')),
                    'type' => in_array($group['type'] ?? '', ['single', 'multiple'], true)
                        ? (string) $group['type']
                        : 'single',
                    'required' => (bool) ($group['required'] ?? false),
                    'choices' => $choices,
                ];
            })
            ->filter(fn (array $group): bool => filled($group['name']) && $group['choices'] !== [])
            ->values()
            ->all();
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     title: string,
         *     slug: string,
         *     price?: float|string|null,
         *     compare_price?: float|string|null,
         *     meal_options?: list<array<string, mixed>>|null,
         *     category_ids?: list<int>|null,
         *     published: bool
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): MenuItemResource
    {
        return (new MenuItemResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
