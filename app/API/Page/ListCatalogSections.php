<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Support\ContentType;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists configured content types with the tenant's enabled page sections.
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
        $enabled = $contentTypes->all($tenant)->pluck('slug')->all();

        $options = $contentTypes->managedSections()
            ->map(fn (ContentType $type): array => [
                'slug' => $type->slug,
                'name' => $type->name,
                'description' => $type->description,
                'icon' => $type->icon,
                'icon_url' => asset($type->icon),
                'color' => $type->color,
                'section' => $type->section,
                'enabled' => in_array($type->slug, $enabled, true),
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
