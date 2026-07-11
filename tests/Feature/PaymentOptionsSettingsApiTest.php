<?php

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForPaymentOptionsSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'pay-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access payment options settings', function () {
    $this->getJson('/api/settings/payment-options')->assertUnauthorized();
    $this->putJson('/api/settings/payment-options/bank-transfer/active', ['active' => true])->assertUnauthorized();
});

test('owner can list and toggle payment options', function () {
    [$user, $tenant] = createUserWithTenantForPaymentOptionsSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/payment-options')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['slug', 'name', 'description', 'icon', 'active', 'settings'],
            ],
        ]);

    $this->actingAs($user)
        ->putJson('/api/settings/payment-options/bank-transfer/active', [
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.slug', 'bank-transfer')
        ->assertJsonPath('data.active', true)
        ->assertJsonStructure(['message']);

    setCurrentTenant($tenant);
    expect((bool) data_get(Setting::paymentMethod('bank-transfer'), 'active'))->toBeTrue();
});

test('owner can update bank transfer settings and preserve active', function () {
    [$user, $tenant] = createUserWithTenantForPaymentOptionsSettings();

    setCurrentTenant($tenant);
    Setting::savePaymentMethod('bank-transfer', ['accounts' => []], true);

    $this->actingAs($user)
        ->putJson('/api/settings/payment-options/bank-transfer', [
            'accounts' => [
                [
                    'bank_name' => 'الراجحي',
                    'account_name' => 'متجري',
                    'iban' => 'SA0380000000608010167519',
                    'account_number' => '123456',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.settings.accounts.0.bank_name', 'الراجحي');

    setCurrentTenant($tenant);
    $saved = Setting::paymentMethod('bank-transfer');

    expect((bool) data_get($saved, 'active'))->toBeTrue()
        ->and(data_get($saved, 'accounts.0.id'))->not->toBeEmpty();
});

test('payment option settings validate custom label', function () {
    [$user] = createUserWithTenantForPaymentOptionsSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/payment-options/custom', [
            'label' => '',
            'description' => 'desc',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['label']);
});

test('users without a tenant cannot access payment options settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/payment-options')
        ->assertForbidden();
});
