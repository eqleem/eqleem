<?php

namespace App\API\DigitalServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalServices\Concerns\ResolvesDigitalService;
use App\Http\Resources\DigitalServiceResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Clones an existing digital service as a new draft.
 */
class CloneDigitalService
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesDigitalService;

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

        $original = $this->findDigitalService($uuid);
        $title = $this->clonedTitle($original->title);
        $slug = $this->uniqueDigitalServiceSlug(Str::slug($title));

        $clone = Content::query()->create([
            'tenant_id' => $original->tenant_id,
            'type' => $this->digitalServiceType(),
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'active' => false,
            'data' => $original->data,
        ]);

        $categoryIds = $original->taxonomiesOfType('digital_service_category')
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        if ($categoryIds !== []) {
            $clone->syncTaxonomiesOfType('digital_service_category', $categoryIds);
        }

        foreach ($original->getMedia('digital-service-media') as $media) {
            $media->copy($clone, 'digital-service-media');
        }

        return $clone->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): DigitalServiceResource
    {
        return (new DigitalServiceResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => 'تم تكرار الخدمة.',
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

        while (Content::query()->type($this->digitalServiceType())->where('title', $base.' '.$this->formatArabicNumber($number))->exists()) {
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
