<?php

use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantForThemeDesignTab(): Tenant
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

    setCurrentTenant($tenant);

    return $tenant;
}

it('renders header image field with square and free crop options when cropShape is both', function () {
    createTenantForThemeDesignTab();

    $theme = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'slug' => 'default',
        'name' => 'Default',
        'meta' => [],
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    Livewire::test('admin::page.tabs.design', ['tab' => []])
        ->set('selectedThemeId', $theme->id)
        ->call('selectTheme', $theme->id)
        ->assertSee('صورة الهيدر')
        ->assertSee('قص الصورة')
        ->assertSee('جاري المعالجة...')
        ->assertSeeHtml('fileCrop');
});
