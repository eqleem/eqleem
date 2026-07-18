<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OnboardingResource;
use App\Models\Tenant;
use App\Support\Onboarding;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Permanently dismisses the new-user onboarding wizard after completion.
 */
class DismissOnboardingWizard
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, Onboarding $onboarding): array
    {
        $progress = $onboarding->forTenant($tenant);

        if ($progress['percentage'] < 100) {
            throw new UnprocessableEntityHttpException('أكمل جميع الخطوات قبل إنهاء الإعداد.');
        }

        $onboarding->dismiss($tenant);

        return GetOnboarding::make()->handle($tenant->fresh(), $onboarding);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, Onboarding $onboarding): array
    {
        return $this->handle($this->currentDashboardTenant($request), $onboarding);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): OnboardingResource
    {
        return (new OnboardingResource($payload))
            ->additional([
                'message' => 'تم إنهاء الإعداد بنجاح',
            ]);
    }
}
