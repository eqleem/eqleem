<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\WelcomeWidgetResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\PageCompletion;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Adds a social link from the welcome-widget completion step.
 */
class AddWelcomeSocialLink
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'network' => ['required', 'string', Rule::in(array_keys(config('social-networks', [])))],
            'url' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array{network: string, url: string}  $data
     * @return array<string, mixed>
     */
    public function handle(User $user, Tenant $tenant, array $data, PageCompletion $pageCompletion): array
    {
        app(TenantProfileService::class)->addSocialLink($tenant, $data['network'], $data['url']);

        return GetWelcomeWidget::make()->handle($user, $tenant->fresh(), $pageCompletion);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, PageCompletion $pageCompletion): array
    {
        /** @var User $user */
        $user = $request->user();
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{network: string, url: string} $validated */
        $validated = $request->validated();

        return $this->handle($user, $tenant, $validated, $pageCompletion);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): WelcomeWidgetResource
    {
        return (new WelcomeWidgetResource($payload))
            ->additional([
                'message' => 'تمت إضافة رابط التواصل بنجاح',
            ]);
    }
}
