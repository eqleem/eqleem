<?php

namespace App\API\Portfolio;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves portfolio section customize settings.
 */
class UpdatePortfolioSettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

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
            'section_title' => ['required', 'string', 'min:2', 'max:255'],
            'section_description' => ['required', 'string', 'min:2', 'max:500'],
        ];
    }

    /**
     * @param  array{section_title: string, section_description: string}  $data
     * @return array{section_title: string, section_description: string}
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        Setting::saveForSlug(Setting::PORTFOLIO_SETTINGS_SLUG, [
            'section_title' => $data['section_title'],
            'section_description' => $data['section_description'],
        ]);

        return GetPortfolioSettings::make()->handle($tenant);
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{section_title: string, section_description: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array{section_title: string, section_description: string}  $settings
     * @return array{data: array{section_title: string, section_description: string}, message: string}
     */
    public function jsonResponse(array $settings): array
    {
        return [
            'data' => $settings,
            'message' => __('Saved'),
        ];
    }
}
