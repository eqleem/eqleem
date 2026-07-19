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
     * @return array{data: list<array<string, mixed>>, enabled: list<string>, content_enabled: list<string>}
     */
    public function handle(Tenant $tenant, ContentTypeRegistry $contentTypes): array
    {
        $enabled = $contentTypes->all($tenant)->pluck('slug')->all();
        $rawPreferences = data_get($tenant->config, 'enabled_content_types');
        $hasPreferences = is_array($rawPreferences);
        $managedSlugs = $contentTypes->managedSections()->pluck('slug')->all();

        // Strict tenant preferences for "add content" UIs. Legacy catalog prefs only
        // listed sellable types; non-sellable still appear in nav until page sections
        // are configured, but must not show up as addable content types.
        $contentEnabled = $hasPreferences
            ? collect($rawPreferences)
                ->filter(fn (mixed $slug): bool => is_string($slug) && in_array($slug, $managedSlugs, true))
                ->values()
                ->all()
            : collect($enabled)
                ->filter(fn (string $slug): bool => in_array($slug, $managedSlugs, true))
                ->values()
                ->all();

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
            'content_enabled' => $contentEnabled,
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, enabled: list<string>, content_enabled: list<string>}
     */
    public function asController(ActionRequest $request, ContentTypeRegistry $contentTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $contentTypes);
    }

    /**
     * @param  array{data: list<array<string, mixed>>, enabled: list<string>, content_enabled: list<string>}  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
