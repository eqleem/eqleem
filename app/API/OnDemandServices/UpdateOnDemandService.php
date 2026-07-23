<?php

namespace App\API\OnDemandServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\OnDemandServices\Concerns\ResolvesOnDemandService;
use App\Http\Resources\OnDemandServiceResource;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\OnDemandUnit;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates an on-demand service's fields (title, body, unit pricing, publish state).
 */
class UpdateOnDemandService
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesOnDemandService;

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
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'unit_type' => ['required', 'string', OnDemandUnit::rule()],
            'unit_label' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn (): bool => request()->input('unit_type') === OnDemandUnit::Other),
            ],
            'active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('active')) {
            $request->merge([
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
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
     *     price?: float|string|null,
     *     compare_price?: float|string|null,
     *     unit_type: string,
     *     unit_label?: string|null,
     *     active: bool
     * }  $data
     */
    public function handle(Tenant $tenant, string $uuid, array $data): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findOnDemandService($uuid);
        $payload = $content->data ?? [];

        $unitType = (string) $data['unit_type'];
        $unitLabel = $unitType === OnDemandUnit::Other
            ? trim((string) ($data['unit_label'] ?? ''))
            : '';

        $payload['subtitle'] = (string) ($data['subtitle'] ?? '');
        $payload['body'] = (string) ($data['body'] ?? '');
        $payload['editor_mode'] = (string) ($data['editor_mode'] ?? data_get($payload, 'editor_mode', 'html'));
        $payload['price'] = filled($data['price'] ?? null) ? money_minor($data['price']) : 0;
        $payload['compare_price'] = filled($data['compare_price'] ?? null) ? money_minor($data['compare_price']) : null;
        $payload['unit_type'] = $unitType;
        $payload['unit_label'] = $unitLabel;

        $slug = $this->uniqueOnDemandServiceSlug(
            filled($data['slug']) ? (string) $data['slug'] : Str::slug($data['title']),
            (int) $content->id,
        );

        $active = (bool) $data['active'];

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

        return $content->fresh(['media']);
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
         *     compare_price?: float|string|null,
         *     unit_type: string,
         *     unit_label?: string|null,
         *     active: bool
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated);
    }

    public function jsonResponse(Content $content): OnDemandServiceResource
    {
        return (new OnDemandServiceResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'unit_options' => $this->unitOptions(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
