<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Content}
 */
function createUserTenantAndFormForToggle(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'نماذج تفعيل',
        'handle' => 'forms-toggle-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'نموذج تجريبي',
        'slug' => 'demo-form-'.Str::lower(Str::random(4)),
        'data' => [
            'fields' => [],
        ],
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    return [$user->fresh(), $tenant->fresh(), $form->fresh()];
}

test('owner can toggle form active state', function () {
    [$user, $tenant, $form] = createUserTenantAndFormForToggle();

    $this->actingAs($user)
        ->putJson("/api/forms/{$form->uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.slug', $form->slug);

    expect($form->fresh())
        ->active->toBeFalse()
        ->status->toBe('draft')
        ->published_at->toBeNull();

    $this->actingAs($user)
        ->putJson("/api/forms/{$form->uuid}/active", ['active' => true])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.status', 'published');

    expect($form->fresh())
        ->active->toBeTrue()
        ->status->toBe('published')
        ->published_at->not->toBeNull();
});

test('guests cannot toggle form active state', function () {
    [, , $form] = createUserTenantAndFormForToggle();

    $this->putJson("/api/forms/{$form->uuid}/active", ['active' => false])
        ->assertUnauthorized();
});
