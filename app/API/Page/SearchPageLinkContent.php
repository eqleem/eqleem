<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Support\CtaLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Searches content items for CTA / block-link pickers.
 */
class SearchPageLinkContent
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $typeKeys = array_keys(CtaLink::linkTypeOptions('nav') + CtaLink::linkTypeOptions('block'));

        return [
            'link_type' => ['required', 'string', Rule::in($typeKeys)],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return list<array{id: int, title: string, type: string}>
     */
    public function handle(Tenant $tenant, string $linkType, ?string $search = null): array
    {
        setCurrentTenant($tenant);

        if (! CtaLink::needsContentPicker($linkType)) {
            return [];
        }

        $results = filled($search) && mb_strlen(trim($search)) >= 2
            ? CtaLink::searchContents($linkType, $search)
            : CtaLink::recentContents($linkType);

        return $results
            ->map(fn ($content): array => [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $content->type,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, title: string, type: string}>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{link_type: string, search?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            $validated['link_type'],
            isset($validated['search']) ? trim((string) $validated['search']) : null,
        );
    }

    /**
     * @param  list<array{id: int, title: string, type: string}>  $results
     */
    public function jsonResponse(array $results): JsonResponse
    {
        return response()->json(['data' => $results]);
    }
}
