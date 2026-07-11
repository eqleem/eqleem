<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\LanguageCurrencySettingsResource;
use App\Models\Setting;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns language and currency settings for the current dashboard tenant.
 */
class GetLanguageCurrencySettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $settings = Setting::localeCurrencySettings();

        return [
            ...$settings,
            'languages' => config('locales.languages', []),
            'currencies' => config('locales.currencies', []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        return $this->handle($this->currentDashboardTenant($request));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): LanguageCurrencySettingsResource
    {
        return new LanguageCurrencySettingsResource($payload);
    }
}
