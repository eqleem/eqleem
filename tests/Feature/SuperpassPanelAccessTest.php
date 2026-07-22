<?php

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\StatsOverview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows any authenticated user to access superpass outside production', function () {
    $user = User::factory()->create(['email' => 'random@example.com']);
    $panel = Filament\Facades\Filament::getPanel('superpass');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('allows only approved emails to access superpass in production', function () {
    app()->detectEnvironment(fn (): string => 'production');

    $panel = Filament\Facades\Filament::getPanel('superpass');

    foreach (User::SUPERPASS_ALLOWED_EMAILS as $email) {
        expect(User::factory()->make(['email' => $email])->canAccessPanel($panel))->toBeTrue();
    }

    expect(User::factory()->make(['email' => 'random@example.com'])->canAccessPanel($panel))->toBeFalse();
});

it('links user tenant and client stats to their resource index pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee(UserResource::getUrl('index'), false)
        ->assertSee(TenantResource::getUrl('index'), false)
        ->assertSee(ClientResource::getUrl('index'), false)
        ->assertSee('الأقاليم')
        ->assertSee('العملاء');
});
