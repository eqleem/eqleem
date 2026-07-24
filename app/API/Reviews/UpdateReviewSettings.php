<?php

namespace App\API\Reviews;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves reviews section customize settings.
 */
class UpdateReviewSettings
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
            'per_page' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @param  array{section_title: string, per_page: int}  $data
     * @return array{section_title: string, per_page: int}
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        Setting::saveForSlug(Setting::REVIEW_SETTINGS_SLUG, [
            'section_title' => $data['section_title'],
            'per_page' => (int) $data['per_page'],
        ]);

        return GetReviewSettings::make()->handle($tenant);
    }

    /**
     * @return array{section_title: string, per_page: int}
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{section_title: string, per_page: int} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array{section_title: string, per_page: int}  $settings
     * @return array{data: array{section_title: string, per_page: int}, message: string}
     */
    public function jsonResponse(array $settings): array
    {
        return [
            'data' => $settings,
            'message' => __('Saved'),
        ];
    }
}
