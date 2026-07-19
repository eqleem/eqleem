<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createGoalSecondaryTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد',
    ]);

    $theme = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إفتراضي',
        'slug' => 'default-sec-'.Str::lower(Str::random(4)),
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'sec-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

it('persists secondary on partial then full save', function () {
    [$user, $tenant] = createGoalSecondaryTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/goal', [
            'partial' => true,
            'primary_action_type' => 'whatsapp-chat',
            'secondary_action_type' => null,
        ])
        ->assertSuccessful();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/goal', [
            'partial' => true,
            'primary_action_type' => 'whatsapp-chat',
            'secondary_action_type' => 'call-me',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.goal.secondary_action_type', 'call-me');

    expect(data_get($tenant->fresh()->meta, 'secondary_action_type'))->toBe('call-me');

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/goal', [
            'primary_action_type' => 'whatsapp-chat',
            'secondary_action_type' => 'email-us',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.goal.secondary_action_type', 'email-us');

    expect(data_get($tenant->fresh()->meta, 'secondary_action_type'))->toBe('email-us');

    $this->actingAs($user)
        ->getJson('/api/dashboard/onboarding')
        ->assertSuccessful()
        ->assertJsonPath('data.forms.goal.secondary_action_type', 'email-us');
});

it('does not clear secondary on partial autosave that sends null', function () {
    [$user, $tenant] = createGoalSecondaryTenant();

    $tenant->meta->set('primary_action_type', 'whatsapp-chat');
    $tenant->meta->set('secondary_action_type', 'call-me');
    $tenant->save();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/goal', [
            'partial' => true,
            'primary_action_type' => 'whatsapp-chat',
            'secondary_action_type' => null,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.goal.secondary_action_type', 'call-me');

    expect(data_get($tenant->fresh()->meta, 'secondary_action_type'))->toBe('call-me');
});

it('clears secondary on full save when null is sent', function () {
    [$user, $tenant] = createGoalSecondaryTenant();

    $tenant->meta->set('primary_action_type', 'whatsapp-chat');
    $tenant->meta->set('secondary_action_type', 'call-me');
    $tenant->save();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/goal', [
            'primary_action_type' => 'whatsapp-chat',
            'secondary_action_type' => null,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.goal.secondary_action_type', '');

    expect(data_get($tenant->fresh()->meta, 'secondary_action_type'))->toBeNull();
});
