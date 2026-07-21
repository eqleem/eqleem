<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\Onboarding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('unlocks steps sequentially and reports current step', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $theme = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إفتراضي',
        'slug' => 'default',
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'onboard-unit-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    $progress = app(Onboarding::class)->forTenant($tenant);

    expect($progress['current_step'])->toBe('business')
        ->and($progress['total'])->toBe(6)
        ->and($progress['steps']->firstWhere('key', 'business')['unlocked'])->toBeTrue()
        ->and($progress['steps']->firstWhere('key', 'contact')['unlocked'])->toBeFalse();

    $tenant->name = 'متجر مكتمل';
    $tenant->meta->set('industry', 'retail');
    $tenant->save();

    app(TenantProfileService::class)->saveBio($tenant, 'نبذة');
    app(TenantProfileService::class)->saveLogo($tenant, 'tenant-media/logo.png');

    $progress = app(Onboarding::class)->forTenant($tenant->fresh());

    expect($progress['steps']->firstWhere('key', 'business')['done'])->toBeTrue()
        ->and($progress['steps']->firstWhere('key', 'contact')['unlocked'])->toBeTrue()
        ->and($progress['current_step'])->toBe('contact');
});

it('marks identity done when primary color is saved', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $theme = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إفتراضي',
        'slug' => 'default',
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'onboard-color-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    $onboarding = app(Onboarding::class);

    expect($onboarding->identityDone($tenant))->toBeFalse();

    $tenant->saveThemeSettingsFor((int) $theme->id, [
        'primaryColor' => 'violet',
    ]);

    expect($onboarding->identityDone($tenant->fresh()))->toBeTrue();
});

it('marks orders done when payment is active without verification', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $theme = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إفتراضي',
        'slug' => 'default',
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'onboard-orders-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $onboarding = app(Onboarding::class);

    expect($onboarding->ordersDone($tenant))->toBeFalse()
        ->and($onboarding->verificationDone($tenant))->toBeFalse();

    Setting::savePaymentMethod('cash-on-delivery', [], true);

    expect($onboarding->ordersDone($tenant))->toBeTrue()
        ->and($onboarding->verificationDone($tenant))->toBeFalse();
});
