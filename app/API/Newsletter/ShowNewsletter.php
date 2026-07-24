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
 * Shows a newsletter issue for editing.
 */
class ShowNewsletter
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesNewsletter;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        return $this->findNewsletter($uuid);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): NewsletterResource
    {
        return new NewsletterResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'mail_status_options' => Content::newsletterMailStatusOptions(),
        ]);
    }
}
