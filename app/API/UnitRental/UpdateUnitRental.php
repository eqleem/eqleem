<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\UnitRental\Concerns\ResolvesUnitRental;
use App\Http\Resources\UnitRentalResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates a unit rental's fields (title, body, pricing, categories, calendars, publish state).
 */
class UpdateUnitRental
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesUnitRental;

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
            'price' => ['nullable', 'numeric', 'min:0'],
            'category_ids' => ['sometimes', 'nullable', 'array'],
            'category_ids.*' => [
                'numeric',
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'unit_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
            'calendar_ids' => ['sometimes', 'nullable', 'array'],
            'calendar_ids.*' => [
                'numeric',
                Rule::exists('calendars', 'id')->where(function ($query): void {
                    $query->where('type', 'rental-unit');

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

        foreach (['category_ids', 'calendar_ids'] as $key) {
            if ($request->exists($key) && is_array($request->input($key))) {
                $request->merge([
                    $key => collect($request->input($key))
                        ->map(fn (mixed $id): int => (int) $id)
                        ->filter(fn (int $id): bool => $id > 0)
                        ->values()
                        ->all(),
                ]);
            }
        }
    }

    /**
     * @param  array{
     *     title: string,
     *     subtitle?: string|null,
     *     body?: string|null,
     *     editor_mode?: string,
     *     slug: string,
     *     price?: float|string|null,
     *     category_ids?: list<int>|null,
     *     calendar_ids?: list<int>|null,
     *     published: bool
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findUnitRental($uuid);
        $payload = $content->data ?? [];

        $payload['subtitle'] = (string) ($data['subtitle'] ?? '');
        $payload['body'] = (string) ($data['body'] ?? '');
        $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));
        $payload['price'] = filled($data['price'] ?? null) ? money_minor($data['price']) : 0;

        $slug = $this->uniqueUnitRentalSlug(
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
            'unit_category',
            $this->selectableCategoryIds($data['category_ids'] ?? []),
        );

        $content->calendars()->sync($this->selectableCalendarIds($data['calendar_ids'] ?? []));

        return $content->fresh(['media', 'taxonomies', 'calendars']);
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
         *     price?: float|string|null,
         *     category_ids?: list<int>|null,
         *     calendar_ids?: list<int>|null,
         *     published: bool
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): UnitRentalResource
    {
        return (new UnitRentalResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'calendar_options' => $this->calendarOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
