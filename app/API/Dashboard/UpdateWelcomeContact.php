<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\WelcomeWidgetResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\PageCompletion;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves welcome-widget contact details.
 */
class UpdateWelcomeContact
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @param  array{phone: string, email: string, country: string, city: string}  $data
     * @return array<string, mixed>
     */
    public function handle(User $user, Tenant $tenant, array $data, PageCompletion $pageCompletion): array
    {
        app(TenantProfileService::class)->saveContact($tenant, $data);

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

        /** @var array{phone: string, email: string, country: string, city: string} $validated */
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
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
