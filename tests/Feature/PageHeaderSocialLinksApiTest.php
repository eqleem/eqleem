<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Block}
 */
function createUserWithTenantForHeaderSocial(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'header-social-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    $tenant->meta->set('contact', [
        'phone' => '',
        'email' => '',
        'whatsapp' => '',
        'country' => '',
        'city' => '',
    ]);
    $tenant->meta->set('social_links', []);
    $tenant->save();

    $header = Block::queryForTenantRoots()
        ->where('type', 'header')
        ->firstOrFail();

    return [$user->fresh(), $tenant->fresh(), $header];
}

test('guests cannot manage header social links', function () {
    $this->postJson('/api/page/header/social', [
        'network' => 'twitter',
        'url' => 'https://twitter.com/eqleem',
    ])->assertUnauthorized();

    $this->putJson('/api/page/header/social/reorder', [
        'order' => ['a'],
    ])->assertUnauthorized();

    $this->deleteJson('/api/page/header/social/abc')->assertUnauthorized();
});

test('owner can add delete and reorder header social links', function () {
    [$user, $tenant] = createUserWithTenantForHeaderSocial();

    $add = $this->actingAs($user)
        ->postJson('/api/page/header/social', [
            'network' => 'twitter',
            'url' => 'https://twitter.com/eqleem',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'تمت إضافة رابط التواصل بنجاح');

    $links = $add->json('data');
    expect($links)->toHaveCount(1)
        ->and($links[0]['network'])->toBe('twitter')
        ->and($links[0]['url'])->toBe('https://twitter.com/eqleem');

    $this->actingAs($user)
        ->postJson('/api/page/header/social', [
            'network' => 'instagram',
            'url' => 'https://instagram.com/eqleem',
        ])
        ->assertSuccessful();

    setCurrentTenant($tenant);
    $all = app(TenantProfileService::class)->socialLinks($tenant->fresh())->values()->all();
    expect($all)->toHaveCount(2);

    $ids = collect($all)->pluck('id')->all();
    $reversed = array_reverse($ids);

    $reordered = $this->actingAs($user)
        ->putJson('/api/page/header/social/reorder', [
            'order' => $reversed,
        ])
        ->assertSuccessful()
        ->json('data');

    expect(collect($reordered)->pluck('id')->all())->toBe($reversed);

    $this->actingAs($user)
        ->deleteJson('/api/page/header/social/'.$ids[0])
        ->assertSuccessful();

    setCurrentTenant($tenant);
    expect(app(TenantProfileService::class)->socialLinks($tenant->fresh()))->toHaveCount(1);
});

test('header block editor payload includes social links for the modal', function () {
    [$user, $tenant, $header] = createUserWithTenantForHeaderSocial();

    setCurrentTenant($tenant);
    app(TenantProfileService::class)->addSocialLink($tenant, 'youtube', 'https://youtube.com/@eqleem');

    $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$header->id)
        ->assertSuccessful()
        ->assertJsonPath('data.editor.type', 'header')
        ->assertJsonPath('data.editor.social_links.0.network', 'youtube')
        ->assertJsonPath('data.editor.social_links.0.url', 'https://youtube.com/@eqleem')
        ->assertJsonStructure([
            'data' => [
                'editor' => [
                    'social_links',
                    'social_networks' => [
                        '*' => ['key', 'label', 'icon'],
                    ],
                ],
            ],
        ]);
});

test('header social link validation requires a known network and non-empty value', function () {
    [$user] = createUserWithTenantForHeaderSocial();

    $this->actingAs($user)
        ->postJson('/api/page/header/social', [
            'network' => 'not-a-network',
            'url' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['network', 'url']);
});

test('owner can add a social handle without a full url', function () {
    [$user] = createUserWithTenantForHeaderSocial();

    $this->actingAs($user)
        ->postJson('/api/page/header/social', [
            'network' => 'twitter',
            'url' => '@eqleem',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.0.network', 'twitter')
        ->assertJsonPath('data.0.url', '@eqleem');
});
