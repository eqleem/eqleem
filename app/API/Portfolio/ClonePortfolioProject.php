<?php

namespace App\API\Portfolio;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Portfolio\Concerns\ResolvesPortfolioProject;
use App\Http\Resources\PortfolioProjectResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Clones an existing portfolio project as a new draft.
 */
class ClonePortfolioProject
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPortfolioProject;

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

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $original = $this->findPortfolioProject($uuid);
        $title = $this->clonedTitle($original->title);
        $slug = $this->uniquePortfolioSlug(Str::slug($title));

        $clone = Content::query()->create([
            'tenant_id' => $original->tenant_id,
            'type' => $this->portfolioType(),
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'active' => false,
            'data' => $original->data,
        ]);

        $categoryIds = $original->taxonomiesOfType('portfolio_category')
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        if ($categoryIds !== []) {
            $clone->syncTaxonomiesOfType('portfolio_category', $categoryIds);
        }

        foreach ($original->getMedia('portfolio-media') as $media) {
            $media->copy($clone, 'portfolio-media');
        }

        return $clone->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): PortfolioProjectResource
    {
        $content->migrateLegacyPortfolioImagesIfNeeded();

        return (new PortfolioProjectResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => 'تم نسخ المشروع.',
        ]);
    }

    private function clonedTitle(string $title): string
    {
        $base = $title;
        $startNumber = 2;

        if (preg_match('/^(.+?) ([\d٠-٩]+)$/u', $title, $matches)) {
            $base = $matches[1];
            $startNumber = $this->parseArabicNumber($matches[2]) + 1;
        }

        $number = $startNumber;

        while (Content::query()->type($this->portfolioType())->where('title', $base.' '.$this->formatArabicNumber($number))->exists()) {
            $number++;
        }

        return $base.' '.$this->formatArabicNumber($number);
    }

    private function formatArabicNumber(int $number): string
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            (string) $number,
        );
    }

    private function parseArabicNumber(string $value): int
    {
        $western = str_replace(
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $value,
        );

        return (int) $western;
    }
}
