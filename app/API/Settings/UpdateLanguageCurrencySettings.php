<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\LanguageCurrencySettingsResource;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates language and currency settings for the current dashboard tenant.
 */
class UpdateLanguageCurrencySettings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $languageKeys = array_keys(config('locales.languages', []));
        $currencyKeys = array_keys(config('locales.currencies', []));

        return [
            'default_language' => ['required', 'string', Rule::in(request()->input('available_languages', []))],
            'default_currency' => ['required', 'string', Rule::in(request()->input('available_currencies', []))],
            'available_languages' => ['required', 'array', 'min:1'],
            'available_languages.*' => ['required', 'string', Rule::in($languageKeys)],
            'available_currencies' => ['required', 'array', 'min:1'],
            'available_currencies.*' => ['required', 'string', Rule::in($currencyKeys)],
        ];
    }

    /**
     * @param  array{default_language: string, default_currency: string, available_languages: list<string>, available_currencies: list<string>}  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        Setting::saveLocaleCurrencySettings([
            'default_language' => $data['default_language'],
            'default_currency' => $data['default_currency'],
            'available_languages' => array_values($data['available_languages']),
            'available_currencies' => array_values($data['available_currencies']),
        ]);

        return GetLanguageCurrencySettings::make()->handle($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{default_language: string, default_currency: string, available_languages: list<string>, available_currencies: list<string>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): LanguageCurrencySettingsResource
    {
        return (new LanguageCurrencySettingsResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
