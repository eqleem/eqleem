<?php

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createTenantForBrandMark(): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    return Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر العلامة',
        'handle' => 'brand-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);
}

it('saves and reads an emoji brand mark', function () {
    $tenant = createTenantForBrandMark();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'emoji',
        'value' => '🚀',
    ]);

    $mark = $profile->brandMark($tenant->fresh());

    expect($mark['type'])->toBe('emoji')
        ->and($mark['value'])->toBe('🚀')
        ->and($profile->hasLogo($tenant->fresh()))->toBeTrue()
        ->and($profile->logo($tenant->fresh()))->toStartWith('data:image/svg+xml');
});

it('saves and reads an icon brand mark with color', function () {
    $tenant = createTenantForBrandMark();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'icon',
        'value' => 'tabler:chart-line',
        'color' => '#2563EB',
    ]);

    $mark = $profile->brandMark($tenant->fresh());

    expect($mark['type'])->toBe('icon')
        ->and($mark['value'])->toBe('tabler:chart-line')
        ->and($mark['color'])->toBe('#2563eb');
});

it('saves an icon brand mark with black or inherited text color', function () {
    $tenant = createTenantForBrandMark();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'icon',
        'value' => 'tabler:home',
        'color' => '#000000',
    ]);

    expect($profile->brandMark($tenant->fresh())['color'])->toBe('#000000');

    $profile->saveBrandMark($tenant->fresh(), [
        'type' => 'icon',
        'value' => 'tabler:home',
        'color' => '',
    ]);

    expect($profile->brandMark($tenant->fresh())['color'])->toBe('');
});

it('clears a brand mark', function () {
    $tenant = createTenantForBrandMark();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'emoji',
        'value' => '🎯',
    ]);

    $profile->clearBrandMark($tenant->fresh());

    $mark = $profile->brandMark($tenant->fresh());

    expect($mark['type'])->toBe('image')
        ->and($profile->hasLogo($tenant->fresh()))->toBeFalse();
});
