<?php

namespace App\API\Forms;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Forms\Concerns\ResolvesForm;
use App\Http\Resources\FormResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Clones an existing form as a new draft.
 */
class CloneForm
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesForm;

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

        $original = $this->findForm($uuid);
        $title = $this->clonedTitle($original->title);
        $slug = $this->uniqueFormSlug(Str::slug($title));

        return Content::query()->create([
            'tenant_id' => $original->tenant_id,
            'type' => $this->formType(),
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'active' => false,
            'data' => $original->data,
        ]);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): FormResource
    {
        return (new FormResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'field_type_options' => $this->fieldTypeOptions(),
        ]))->additional([
            'message' => 'تم نسخ النموذج.',
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

        while (Content::query()->type($this->formType())->where('title', $base.' '.$this->formatArabicNumber($number))->exists()) {
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
