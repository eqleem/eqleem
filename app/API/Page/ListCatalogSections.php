<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists sellable catalog content types with the tenant's enabled selections.
 */
class ListCatalogSections
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array{data: list<array<string, mixed>>, enabled: list<string>}
     */
    public function handle(Tenant $tenant, ContentTypeRegistry $contentTypes): array
    {
        $enabled = data_get($tenant->config, 'enabled_content_types');
        $hasPrefs = is_array($enabled);

        $options = $contentTypes->configured()
            ->filter(fn ($type): bool => $type->sellable)
            ->map(fn ($type): array => [
                'slug' => $type->slug,
                'name' => $type->name,
                'description' => $type->description,
                'icon' => $type->icon,
                'icon_url' => asset($type->icon),
                'color' => $type->color,
                'enabled' => $hasPrefs
                    ? in_array($type->slug, $enabled, true)
                    : $type->active,
            ])
            ->values()
            ->all();

        $selected = collect($options)
            ->filter(fn (array $option): bool => $option['enabled'])
            ->pluck('slug')
            ->values()
            ->all();

        return [
            'data' => $options,
            'enabled' => $selected,
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, enabled: list<string>}
     */
    public function asController(ActionRequest $request, ContentTypeRegistry $contentTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $contentTypes);
    }

    /**
     * @param  array{data: list<array<string, mixed>>, enabled: list<string>}  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
