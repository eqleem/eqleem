<?php

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PaymentMethodRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantWithUserForPaymentOptions(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user, $tenant];
}

it('registers payment options in settings config', function () {
    expect(config('settings.payment-options.slug'))->toBe('payment-options')
        ->and(config('settings.payment-options.name'))->toBe('وسائل الدفع')
        ->and(config('settings.payment-options.order'))->toBe(10)
        ->and(config('settings.payment-options.components.index'))->toBe('admin::settings.payment-options.payment-options');
});

it('defines all payment methods in config', function () {
    $methods = app(PaymentMethodRegistry::class)->all();

    expect($methods)->toHaveCount(6)
        ->and($methods->pluck('slug')->all())->toBe([
            'bank-transfer',
            'credit-card',
            'cash-on-delivery',
            'tabby',
            'tamara',
            'custom',
        ]);
});

it('toggles a payment method active state for the tenant', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.payment-options')
        ->call('toggleActive', 'cash-on-delivery')
        ->assertHasNoErrors();

    $saved = Setting::paymentMethod('cash-on-delivery');

    expect($saved['active'])->toBeTrue();

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.payment-options')
        ->call('toggleActive', 'cash-on-delivery')
        ->assertHasNoErrors();

    expect(Setting::paymentMethod('cash-on-delivery')['active'])->toBeFalse();
});

it('saves cash on delivery settings from the modal', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.payment-options')
        ->call('toggleActive', 'cash-on-delivery');

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.modals.cash-on-delivery', ['slug' => 'cash-on-delivery'])
        ->set('minLimit', '99')
        ->set('label', 'الدفع نقداً عند الاستلام')
        ->set('description', 'للطلبات أكثر من 100 ريال')
        ->call('submit')
        ->assertHasNoErrors();

    $saved = Setting::paymentMethod('cash-on-delivery');

    expect($saved['active'])->toBeTrue()
        ->and($saved['min_limit'])->toEqual(99)
        ->and($saved['label'])->toBe('الدفع نقداً عند الاستلام')
        ->and($saved['description'])->toBe('للطلبات أكثر من 100 ريال');
});

it('preserves active state when saving from the modal', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Setting::savePaymentMethod('cash-on-delivery', [], false);

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.modals.cash-on-delivery', ['slug' => 'cash-on-delivery'])
        ->set('label', 'الدفع نقداً')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Setting::paymentMethod('cash-on-delivery')['active'])->toBeFalse()
        ->and(Setting::paymentMethod('cash-on-delivery')['label'])->toBe('الدفع نقداً');
});

it('saves bank transfer accounts for the tenant', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.payment-options')
        ->call('toggleActive', 'bank-transfer');

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.modals.bank-transfer', ['slug' => 'bank-transfer'])
        ->set('accounts', [[
            'id' => 'acc-1',
            'bank_name' => 'الراجحي',
            'account_name' => 'شركة اختبار',
            'iban' => 'SA1234567890123456789012',
            'account_number' => '1234567890',
        ]])
        ->call('submit')
        ->assertHasNoErrors();

    $saved = Setting::paymentMethod('bank-transfer');

    expect($saved['active'])->toBeTrue()
        ->and($saved['accounts'])->toHaveCount(1)
        ->and($saved['accounts'][0]['bank_name'])->toBe('الراجحي')
        ->and($saved['accounts'][0]['iban'])->toBe('SA1234567890123456789012');
});

it('adds a bank account through the account form', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.modals.bank-transfer', ['slug' => 'bank-transfer'])
        ->call('openAccountForm')
        ->set('bankName', 'الراجحي')
        ->set('accountName', 'شركة اختبار')
        ->set('iban', 'SA1234567890123456789012')
        ->set('accountNumber', '1234567890')
        ->call('saveAccount')
        ->assertHasNoErrors()
        ->assertCount('accounts', 1)
        ->call('submit')
        ->assertHasNoErrors();

    $saved = Setting::paymentMethod('bank-transfer');

    expect($saved['accounts'])->toHaveCount(1)
        ->and($saved['accounts'][0]['bank_name'])->toBe('الراجحي')
        ->and($saved['accounts'][0]['account_name'])->toBe('شركة اختبار');
});

it('renders the payment options settings page', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.payment-options.payment-options')
        ->assertSee('وسائل الدفع')
        ->assertSee('التحويل البنكي')
        ->assertSee('تابي')
        ->assertSee('تمارا')
        ->assertSee('مخصص');
});

it('renders payment options tab in store index using settings component', function () {
    [$user] = createTenantWithUserForPaymentOptions();

    Livewire::actingAs($user)
        ->withQueryParams(['section' => 'payment-options'])
        ->test('admin::page.content.store.index', [
            'contentType' => config('content-types.store'),
        ])
        ->assertSet('activeStoreTab', 'payment-options')
        ->assertSee(config('settings.payment-options.name'))
        ->assertSeeHtml('wire:name="'.config('settings.payment-options.components.index').'"');
});
