<?php

use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantForCompletionBasicInfo(): Tenant
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

it('renders logo file crop without show avatar toggle', function () {
    createTenantForCompletionBasicInfo();

    $headerBlock = Block::findSingleton('header');

    expect($headerBlock)->not->toBeNull();

    Livewire::test('admin::home.completion-basic-info', ['headerBlockId' => $headerBlock->id])
        ->assertSee('رفع شعار')
        ->assertDontSee('عرض الشعار')
        ->assertSeeHtml('fileCrop');
});

it('saves basic info and preserves show avatar setting', function () {
    createTenantForCompletionBasicInfo();

    $headerBlock = Block::findSingleton('header');
    $headerBlock->update([
        'data' => [
            'show_avatar' => false,
            'bio' => '',
        ],
    ]);

    Livewire::test('admin::home.completion-basic-info', ['headerBlockId' => $headerBlock->id])
        ->set('name', 'Updated Tenant Name')
        ->set('bio', 'نبذة محدثة')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('page-completion-updated')
        ->assertDispatched('closemodal', modal: 'home-step-basic-info');

    $headerBlock->refresh();

    expect($headerBlock->data['show_avatar'])->toBeFalse();
    expect($headerBlock->data['bio'])->toBe('نبذة محدثة');
    expect(currentTenant()->fresh()->name)->toBe('Updated Tenant Name');
});
