<?php

use App\Filament\Resources\Plans\Pages\CreatePlan;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Filament\Resources\Plans\PlanResource;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use LucasDotVin\Soulbscription\Models\Subscription;

uses(RefreshDatabase::class);

it('can list create update and delete plans', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(ListPlans::class)
        ->assertSuccessful();

    Livewire::test(CreatePlan::class)
        ->assertSuccessful()
        ->fillForm([
            'label' => 'خطة اختبار',
            'name' => 'test-plan',
            'slug' => 'test-plan',
            'price' => '49.00',
            'grace_days' => 3,
            'periodicity' => 1,
            'periodicity_type' => 'Month',
            'active' => true,
            'is_featured' => true,
            'is_system' => true,
            'meta' => ['description' => 'خطة للاختبار'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $plan = Plan::query()->where('slug', 'test-plan')->first();

    expect($plan)->not->toBeNull()
        ->and($plan->price)->toBe(Money::toMinor(49))
        ->and($plan->active)->toBeTrue()
        ->and($plan->label)->toBe('خطة اختبار');

    Livewire::test(EditPlan::class, ['record' => $plan->getRouteKey()])
        ->assertSuccessful()
        ->fillForm([
            'label' => 'خطة محدّثة',
            'price' => '99.00',
            'active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plan->fresh()->label)->toBe('خطة محدّثة')
        ->and($plan->fresh()->price)->toBe(Money::toMinor(99))
        ->and($plan->fresh()->active)->toBeFalse();

    Livewire::test(EditPlan::class, ['record' => $plan->getRouteKey()])
        ->callAction('delete');

    expect(Plan::query()->find($plan->id))->toBeNull()
        ->and(Plan::withTrashed()->find($plan->id))->not->toBeNull();
});

it('hides free plan subscriptions from the subscriptions resource', function () {
    $user = User::factory()->create();

    $tenantId = DB::table('tenants')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'إقليم مجاني',
        'handle' => 'free-sub-'.Str::lower(Str::random(4)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $freePlan = Plan::query()->create([
        'id' => 1,
        'name' => 'free',
        'slug' => 'free',
        'label' => 'مجانية',
        'price' => 0,
        'grace_days' => 0,
        'is_system' => true,
        'active' => true,
    ]);

    $paidPlan = Plan::query()->create([
        'name' => 'pro-monthly',
        'slug' => 'pro-monthly-'.Str::lower(Str::random(4)),
        'label' => 'برو',
        'price' => 19900,
        'periodicity' => 1,
        'periodicity_type' => 'Month',
        'grace_days' => 0,
        'is_system' => true,
        'active' => true,
    ]);

    $freeSubscriptionId = DB::table('subscriptions')->insertGetId([
        'plan_id' => $freePlan->id,
        'subscriber_type' => Tenant::class,
        'subscriber_id' => $tenantId,
        'started_at' => now()->toDateString(),
        'expired_at' => null,
        'canceled_at' => null,
        'suppressed_at' => null,
        'was_switched' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $paidSubscriptionId = DB::table('subscriptions')->insertGetId([
        'plan_id' => $paidPlan->id,
        'subscriber_type' => Tenant::class,
        'subscriber_id' => $tenantId,
        'started_at' => now()->toDateString(),
        'expired_at' => now()->addMonth(),
        'canceled_at' => null,
        'suppressed_at' => null,
        'was_switched' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $freeSubscription = Subscription::query()->withoutGlobalScopes()->findOrFail($freeSubscriptionId);
    $paidSubscription = Subscription::query()->withoutGlobalScopes()->findOrFail($paidSubscriptionId);

    $this->actingAs($user);

    Livewire::test(ListSubscriptions::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$paidSubscription])
        ->assertCanNotSeeTableRecords([$freeSubscription]);
});

it('allows full plan resource authorization', function () {
    $plan = Plan::query()->create([
        'name' => 'auth-plan',
        'slug' => 'auth-plan',
        'label' => 'Auth',
        'price' => 1000,
        'grace_days' => 0,
        'active' => true,
    ]);

    expect(PlanResource::canCreate())->toBeTrue()
        ->and(PlanResource::canEdit($plan))->toBeTrue()
        ->and(PlanResource::canDelete($plan))->toBeTrue();
});
