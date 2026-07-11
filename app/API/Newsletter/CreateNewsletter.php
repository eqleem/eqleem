<?php

namespace App\API\Newsletter;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Newsletter\Concerns\ResolvesNewsletter;
use App\Http\Resources\NewsletterResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a draft newsletter issue (title only).
 */
class CreateNewsletter
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesNewsletter;

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
        ];
    }

    public function handle(Tenant $tenant, string $title): Content
    {
        setCurrentTenant($tenant);

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $this->newsletterType(),
            'title' => $title,
            'slug' => $this->uniqueNewsletterSlug($this->slugifyTitle($title)),
            'status' => 'draft',
            'active' => true,
            'data' => [
                'mail_status' => 'draft',
            ],
        ]);
    }

    public function asController(ActionRequest $request): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{title: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, trim($validated['title']));
    }

    public function jsonResponse(Content $content): NewsletterResource
    {
        return (new NewsletterResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
            'mail_status_options' => Content::newsletterMailStatusOptions(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
