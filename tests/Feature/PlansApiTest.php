<?php

use App\Actions\SubscribeTenantToPlan;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

/**
 * @return array{0: User, 1: Tenant}
 */
function createDashboardOwnerForPlans(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'plan-api-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();
    SubscribeTenantToPlan::run($tenant, $freePlan);

    return [$user->fresh(), $tenant->fresh(['subscription.plan'])];
}

test('guests cannot access plans api', function () {
    $this->getJson('/api/plans')->assertUnauthorized();
    $this->postJson('/api/plans/subscribe-free')->assertUnauthorized();
    $this->getJson('/api/plans/1/checkout')->assertUnauthorized();
});

test('owner can list subscription plans for billing period', function () {
    [$user, $tenant] = createDashboardOwnerForPlans();

    $response = $this->actingAs($user)
        ->getJson('/api/plans?billing_period=monthly')
        ->assertSuccessful()
        ->assertJsonPath('data.billing_period', 'monthly')
        ->assertJsonStructure([
            'data' => [
                'billing_period',
                'current_plan_id',
                'app_name',
                'plans' => [
                    ['id', 'title', 'price', 'price_formatted', 'free', 'current', 'features', 'audience'],
                ],
                'faqs',
            ],
        ]);

    $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();

    expect($tenant->fresh(['subscription.plan'])->subscription?->plan_id)->toBe($freePlan->id);
    expect(collect($response->json('data.plans'))->pluck('title', 'tier')->all())->toBe([
        'free' => 'بداية',
        'basic' => 'إنطلاق',
        'pro' => 'نمو',
    ]);
});

test('owner can subscribe to free plan via api', function () {
    [$user] = createDashboardOwnerForPlans();

    $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();

    $this->actingAs($user)
        ->postJson('/api/plans/subscribe-free')
        ->assertSuccessful()
        ->assertJsonPath('message', 'تم تفعيل الباقة المجانية.')
        ->assertJsonPath('data.current_plan_id', $freePlan->id);
});

test('owner can fetch moyasar checkout config for paid plan', function () {
    [$user] = createDashboardOwnerForPlans();

    config(['services.moyasar.publishable_key' => 'pk_test_example']);

    $plan = Plan::query()->where('slug', 'basic-monthly')->firstOrFail();

    $this->actingAs($user)
        ->getJson('/api/plans/'.$plan->id.'/checkout')
        ->assertSuccessful()
        ->assertJsonPath('data.plan.id', $plan->id)
        ->assertJsonPath('data.plan.title', 'إنطلاق')
        ->assertJsonPath('data.checkout.amount', $plan->price)
        ->assertJsonPath('data.checkout.publishable_api_key', 'pk_test_example')
        ->assertJsonPath('data.checkout.metadata.plan_id', $plan->id)
        ->assertJsonPath('data.checkout.callback_url', route('dashboard.payments.moyasar.callback'));
});

test('checkout rejects free plan', function () {
    [$user] = createDashboardOwnerForPlans();

    config(['services.moyasar.publishable_key' => 'pk_test_example']);

    $plan = Plan::query()->where('slug', 'free')->firstOrFail();

    $this->actingAs($user)
        ->getJson('/api/plans/'.$plan->id.'/checkout')
        ->assertNotFound();
});

test('dashboard moyasar callback route is registered before spa catch-all', function () {
    expect(route('dashboard.payments.moyasar.callback'))
        ->toBe(url('/dashboard/payments/moyasar/callback'));
});
