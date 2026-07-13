<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
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
        ->and($progress['steps']->firstWhere('key', 'business')['unlocked'])->toBeTrue()
        ->and($progress['steps']->firstWhere('key', 'contact')['unlocked'])->toBeFalse();

    $tenant->name = 'متجر مكتمل';
    $tenant->meta->set('industry', 'retail');
    $tenant->save();

    $header = Block::findSingleton('header');
    $header?->update([
        'data' => array_merge($header->data ?? [], ['bio' => 'نبذة']),
    ]);

    app(TenantProfileService::class)->saveLogo($tenant, 'tenant-media/logo.png');

    $progress = app(Onboarding::class)->forTenant($tenant->fresh());

    expect($progress['steps']->firstWhere('key', 'business')['done'])->toBeTrue()
        ->and($progress['steps']->firstWhere('key', 'contact')['unlocked'])->toBeTrue()
        ->and($progress['current_step'])->toBe('contact');
});
