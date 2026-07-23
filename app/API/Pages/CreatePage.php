<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a draft content page (custom, contact, FAQ, or about template).
 */
class CreatePage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPage;

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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'template' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(Content::creatablePageTemplates()),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! filled($value)) {
                        return;
                    }

                    $exists = Content::query()
                        ->type($this->pagesType())
                        ->where('template', (string) $value)
                        ->exists();

                    if ($exists) {
                        $fail('هذه الصفحة موجودة بالفعل.');
                    }
                },
            ],
        ];
    }

    public function handle(Tenant $tenant, string $title, ?string $template = null): Content
    {
        setCurrentTenant($tenant);

        $template = filled($template) ? (string) $template : null;
        $data = match ($template) {
            'contact' => Content::defaultContactPageData(),
            'faq' => Content::defaultFaqPageData(),
            'about' => Content::defaultAboutPageData(),
            default => [],
        };

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $this->pagesType(),
            'template' => $template,
            'title' => $title,
            'slug' => $this->uniquePageSlug($this->slugifyTitle($title)),
            'data' => $data,
            'status' => 'draft',
            'active' => false,
        ]);
    }

    public function asController(ActionRequest $request): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{title: string, template?: string|null} $validated */
        $validated = $request->validated();

        $template = isset($validated['template']) && filled($validated['template'])
            ? (string) $validated['template']
            : null;

        return $this->handle($tenant, trim($validated['title']), $template);
    }

    public function jsonResponse(Content $content): PageResource
    {
        return (new PageResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
