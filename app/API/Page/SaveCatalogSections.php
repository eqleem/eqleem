<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves which sellable catalog content types are enabled for the tenant.
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
        $sellableSlugs = app(ContentTypeRegistry::class)->configured()
            ->filter(fn ($type): bool => $type->sellable)
            ->pluck('slug')
            ->all();

        return [
            'enabled' => ['present', 'array'],
            'enabled.*' => ['required', 'string', Rule::in($sellableSlugs)],
        ];
    }

    /**
     * @param  array{enabled: list<string>}  $data
     * @return array{data: list<array<string, mixed>>, enabled: list<string>, message: string}
     */
    public function handle(Tenant $tenant, array $data, ContentTypeRegistry $contentTypes): array
    {
        $config = is_array($tenant->config) ? $tenant->config : [];
        $config['enabled_content_types'] = array_values(array_unique($data['enabled']));
        $tenant->config = $config;
        $tenant->save();

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
