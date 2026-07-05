<?php

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantForLocaleCurrencySettings(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Locale Tenant',
        'handle' => 'locale-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user, $tenant];
}

it('registers language and currency in settings config', function () {
    expect(config('settings.language-currency.slug'))->toBe('language-currency')
        ->and(config('settings.language-currency.name'))->toBe('اللغة والعملة')
        ->and(config('settings.language-currency.order'))->toBe(5)
        ->and(config('settings.language-currency.components.index'))->toBe('admin::settings.info.language-currency');
});

it('defines languages and currencies in locales config', function () {
    expect(config('locales.languages'))->toHaveKey('ar')
        ->and(config('locales.languages'))->toHaveKey('en')
        ->and(config('locales.currencies'))->toHaveKey('SAR')
        ->and(config('locales.currencies'))->toHaveKey('USD')
        ->and(config('locales.defaults.default_language'))->toBe('ar')
        ->and(config('locales.defaults.default_currency'))->toBe('SAR');
});

it('returns default locale currency settings when none are saved', function () {
    createTenantForLocaleCurrencySettings();

    expect(Setting::localeCurrencySettings())->toBe([
        'default_language' => 'ar',
        'default_currency' => 'SAR',
        'available_languages' => ['ar'],
        'available_currencies' => ['SAR'],
    ]);
});

it('saves language and currency settings to the database', function () {
    [$user] = createTenantForLocaleCurrencySettings();

    Livewire::actingAs($user)
        ->test('admin::settings.info.language-currency')
        ->set('availableLanguages', ['ar', 'en'])
        ->set('defaultLanguage', 'en')
        ->set('availableCurrencies', ['SAR', 'USD', 'EUR'])
        ->set('defaultCurrency', 'USD')
        ->call('submit')
        ->assertHasNoErrors();

    $saved = Setting::forSlug(Setting::LOCALE_CURRENCY_SETTINGS_SLUG);

    expect($saved)->not->toBeNull()
        ->and($saved->settings)->toBe([
            'default_language' => 'en',
            'default_currency' => 'USD',
            'available_languages' => ['ar', 'en'],
            'available_currencies' => ['SAR', 'USD', 'EUR'],
        ]);

    expect(Setting::localeCurrencySettings())->toBe([
        'default_language' => 'en',
        'default_currency' => 'USD',
        'available_languages' => ['ar', 'en'],
        'available_currencies' => ['SAR', 'USD', 'EUR'],
    ]);
});

it('loads saved language and currency settings on mount', function () {
    [$user] = createTenantForLocaleCurrencySettings();

    Setting::saveLocaleCurrencySettings([
        'default_language' => 'en',
        'default_currency' => 'AED',
        'available_languages' => ['ar', 'en'],
        'available_currencies' => ['SAR', 'AED'],
    ]);

    Livewire::actingAs($user)
        ->test('admin::settings.info.language-currency')
        ->assertSet('defaultLanguage', 'en')
        ->assertSet('defaultCurrency', 'AED')
        ->assertSet('availableLanguages', ['ar', 'en'])
        ->assertSet('availableCurrencies', ['SAR', 'AED']);
});

it('rejects a default language that is not available', function () {
    [$user] = createTenantForLocaleCurrencySettings();

    Livewire::actingAs($user)
        ->test('admin::settings.info.language-currency')
        ->set('availableLanguages', ['ar'])
        ->set('defaultLanguage', 'en')
        ->call('submit')
        ->assertHasErrors(['defaultLanguage']);
});

it('rejects a default currency that is not available', function () {
    [$user] = createTenantForLocaleCurrencySettings();

    Livewire::actingAs($user)
        ->test('admin::settings.info.language-currency')
        ->set('availableCurrencies', ['SAR'])
        ->set('defaultCurrency', 'USD')
        ->call('submit')
        ->assertHasErrors(['defaultCurrency']);
});

it('resets the default language when it is removed from available languages', function () {
    [$user] = createTenantForLocaleCurrencySettings();

    Livewire::actingAs($user)
        ->test('admin::settings.info.language-currency')
        ->set('availableLanguages', ['ar', 'en'])
        ->set('defaultLanguage', 'en')
        ->set('availableLanguages', ['ar'])
        ->assertSet('defaultLanguage', 'ar');
});

it('renders the language and currency settings page', function () {
    [$user] = createTenantForLocaleCurrencySettings();

    Livewire::actingAs($user)
        ->test('admin::settings.info.language-currency')
        ->assertSee('اللغة الافتراضية')
        ->assertSee('اللغات المتاحة')
        ->assertSee('العملة الافتراضية')
        ->assertSee('العملات المتاحة');
});

it('formats money using the tenant default currency from settings', function () {
    createTenantForLocaleCurrencySettings();

    Setting::saveLocaleCurrencySettings([
        'default_language' => 'ar',
        'default_currency' => 'USD',
        'available_languages' => ['ar'],
        'available_currencies' => ['USD'],
    ]);

    expect(money_currency())->toBe('USD')
        ->and(money_format_plain(9900))->toBe('99'."\u{00A0}".'$')
        ->and(money_symbol())->toBe('$');
});
