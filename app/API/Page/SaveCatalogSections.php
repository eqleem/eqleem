<?php

namespace App\API\Page;

use App\Actions\SyncTenantSections;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves enabled content types and synchronizes their public page section links.
 */
class SaveCatalogSections
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $contentTypeSlugs = app(ContentTypeRegistry::class)->managedSections()
            ->pluck('slug')
            ->all();

        return [
            'enabled' => ['present', 'array'],
            'enabled.*' => ['required', 'string', 'distinct', Rule::in($contentTypeSlugs)],
        ];
    }

    /**
     * @param  array{enabled: list<string>}  $data
     * @return array{data: list<array<string, mixed>>, enabled: list<string>, content_enabled: list<string>, message: string}
     */
    public function handle(Tenant $tenant, array $data, ContentTypeRegistry $contentTypes): array
    {
        $enabled = array_values(array_unique($data['enabled']));

        DB::transaction(function () use ($tenant, $enabled, $contentTypes): void {
            $config = is_array($tenant->config) ? $tenant->config : [];
            $config['enabled_content_types'] = $enabled;
            $config['page_sections_configured'] = true;
            $tenant->config = $config;
            $tenant->save();

            SyncTenantSections::run($tenant, $enabled, $contentTypes);
        });

        $payload = ListCatalogSections::make()->handle($tenant->fresh(), $contentTypes);

        return [
            ...$payload,
            'message' => __('Settings updated successfully.'),
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>, enabled: list<string>, message: string}
     */
    public function asController(ActionRequest $request, ContentTypeRegistry $contentTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{enabled: list<string>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated, $contentTypes);
    }

    /**
     * @param  array{data: list<array<string, mixed>>, enabled: list<string>, message: string}  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
