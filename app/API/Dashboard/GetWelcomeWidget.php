<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\WelcomeWidgetResource;
use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\PageCompletion;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns the home welcome widget payload: greeting, page URL, and completion steps.
 */
class GetWelcomeWidget
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, Tenant $tenant, PageCompletion $pageCompletion): array
    {
        $completion = $pageCompletion->forTenant($tenant);
        $headerBlock = Block::findSingleton('header');
        $headerData = $headerBlock?->data ?? [];
        $profile = app(TenantProfileService::class);

        $nextStep = $completion['steps']->firstWhere('done', false);

        return [
            'greeting' => $this->resolveGreeting(),
            'user_name' => (string) ($user->name ?? 'ضيف'),
            'page_url' => (string) ($tenant->url ?? url('/')),
            'share_text' => 'شاهد صفحة '.(string) ($tenant->name ?? config('app.name')),
            'percentage' => $completion['percentage'],
            'completed_steps' => $completion['completed'],
            'total_steps' => $completion['total'],
            'steps' => $completion['steps']->values()->all(),
            'next_step' => $nextStep,
            'forms' => [
                'basic_info' => [
                    'name' => (string) ($tenant->name ?? ''),
                    'bio' => (string) ($headerData['bio'] ?? ''),
                    'logo' => (string) ($tenant->logo ?? ''),
                ],
                'contact' => $profile->contact($tenant),
                'social_networks' => collect(config('social-networks', []))
                    ->map(fn (array $network): string => $network['label'])
                    ->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, PageCompletion $pageCompletion): array
    {
        /** @var User $user */
        $user = $request->user();
        $tenant = $this->currentDashboardTenant($request);

        return $this->handle($user, $tenant, $pageCompletion);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): WelcomeWidgetResource
    {
        return new WelcomeWidgetResource($payload);
    }

    protected function resolveGreeting(): string
    {
        $hour = (int) now()->format('G');

        if ($hour < 12) {
            return 'صباح الخير';
        }

        return 'مساء الخير';
    }
}
