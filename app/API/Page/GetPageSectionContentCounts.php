<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Block;
use App\Models\Content;
use App\Models\Review;
use App\Models\Tenant;
use App\Support\ContentType;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPageSectionContentCounts
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<int, array{count: int, label: string}>
     */
    public function handle(Tenant $tenant, ContentTypeRegistry $contentTypes): array
    {
        setCurrentTenant($tenant);

        $contentTypeMeta = $contentTypes->configured()
            ->mapWithKeys(function (ContentType $contentType): array {
                $config = config('content-types.'.$contentType->slug, []);

                return [
                    $contentType->slug => [
                        'slug' => $contentType->slug,
                        'model_type' => $contentType->modelType,
                        'singular' => (string) ($config['count_singular'] ?? $contentType->name),
                        'plural' => (string) ($config['count_plural'] ?? $contentType->name),
                    ],
                ];
            });

        $sections = Block::queryForTenantRoots()
            ->userBlocks()
            ->type('block-link')
            ->get(['id', 'data'])
            ->map(function (Block $block) use ($contentTypeMeta): ?array {
                $data = is_array($block->data) ? $block->data : [];
                $slug = (string) ($data['content_type'] ?? '');

                if (($data['link_type'] ?? '') !== 'section' || ! $contentTypeMeta->has($slug)) {
                    return null;
                }

                return [
                    'block_id' => $block->id,
                    ...$contentTypeMeta->get($slug),
                ];
            })
            ->filter()
            ->values();

        if ($sections->isEmpty()) {
            return [];
        }

        $countsBySlug = $this->countsBySlug($sections);

        return $sections
            ->mapWithKeys(function (array $section) use ($countsBySlug): array {
                $count = (int) ($countsBySlug[$section['slug']] ?? 0);

                return [
                    $section['block_id'] => [
                        'count' => $count,
                        'label' => $count === 1 ? $section['singular'] : $section['plural'],
                    ],
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, array{block_id: int, slug: string, model_type: string, singular: string, plural: string}>  $sections
     * @return array<string, int>
     */
    private function countsBySlug(Collection $sections): array
    {
        $counts = [];

        $contentSections = $sections->reject(
            fn (array $section): bool => $section['slug'] === 'reviews'
        );

        if ($contentSections->isNotEmpty()) {
            $countsByType = Content::query()
                ->whereIn('type', $contentSections->pluck('model_type')->unique()->all())
                ->where('active', true)
                ->whereNull('block_id')
                ->whereNull('parent_id')
                ->selectRaw('type, COUNT(*) as aggregate')
                ->groupBy('type')
                ->pluck('aggregate', 'type');

            foreach ($contentSections as $section) {
                $counts[$section['slug']] = (int) ($countsByType[$section['model_type']] ?? 0);
            }
        }

        if ($sections->contains(fn (array $section): bool => $section['slug'] === 'reviews')) {
            $counts['reviews'] = Review::query()->count();
        }

        return $counts;
    }

    /**
     * @return array<int, array{count: int, label: string}>
     */
    public function asController(ActionRequest $request, ContentTypeRegistry $contentTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $contentTypes);
    }

    /**
     * @param  array<int, array{count: int, label: string}>  $counts
     */
    public function jsonResponse(array $counts): JsonResponse
    {
        return response()->json(['data' => $counts]);
    }
}
