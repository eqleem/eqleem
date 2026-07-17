<?php

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createTenantForProfile(bool $skipDefaultImport = false): Tenant
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

    if ($skipDefaultImport) {
        $tenant->meta->set('contact', [
            'phone' => '',
            'email' => '',
            'whatsapp' => '',
            'country' => '',
            'city' => '',
        ]);
        $tenant->meta->set('social_links', []);
        $tenant->save();
    }

    setCurrentTenant($tenant);

    return $tenant;
}

it('saves and retrieves contact information', function () {
    $tenant = createTenantForProfile();
    $service = app(TenantProfileService::class);

    $service->saveContact($tenant, [
        'phone' => '0501234567',
        'email' => 'hello@example.com',
        'whatsapp' => '966501234567',
        'country' => 'السعودية',
        'city' => 'الرياض',
    ]);

    $contact = $service->contact($tenant->fresh());

    expect($contact['phone'])->toBe('0501234567')
        ->and($contact['email'])->toBe('hello@example.com')
        ->and($contact['whatsapp'])->toBe('966501234567')
        ->and($contact['country'])->toBe('السعودية')
        ->and($contact['city'])->toBe('الرياض')
        ->and($tenant->fresh()->phone)->toBe('0501234567')
        ->and($tenant->fresh()->email)->toBe('hello@example.com');
});

it('saves and retrieves bio on tenants.meta', function () {
    $tenant = createTenantForProfile();
    $service = app(TenantProfileService::class);

    $service->saveBio($tenant, 'صفحة إقليم جديدة');

    expect($service->bio($tenant->fresh()))->toBe('صفحة إقليم جديدة')
        ->and(data_get($tenant->fresh()->meta, 'bio'))->toBe('صفحة إقليم جديدة')
        ->and($tenant->fresh()->bio)->toBe('صفحة إقليم جديدة');
});

it('imports legacy header bio into tenants.meta once', function () {
    $tenant = createTenantForProfile();

    $headerBlock = Block::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', 'header')
        ->firstOrFail();

    $headerBlock->update([
        'data' => array_merge($headerBlock->data ?? [], [
            'bio' => 'نبذة من الهيدر',
        ]),
    ]);

    $tenant->meta->forget('bio');
    $tenant->meta->forget('bio_saved');
    $tenant->save();

    $service = app(TenantProfileService::class);

    expect($service->bio($tenant->fresh()))->toBe('نبذة من الهيدر')
        ->and((bool) data_get($tenant->fresh()->meta, 'bio_saved'))->toBeTrue();
});

it('merges partial contact updates without clearing other fields', function () {
    $tenant = createTenantForProfile();
    $service = app(TenantProfileService::class);

    $service->saveContact($tenant, [
        'phone' => '0501234567',
        'email' => 'hello@example.com',
        'whatsapp' => '966501234567',
        'country' => 'السعودية',
        'city' => 'الرياض',
    ]);

    $service->saveContact($tenant->fresh(), [
        'country' => 'الإمارات',
        'city' => 'دبي',
    ]);

    $contact = $service->contact($tenant->fresh());

    expect($contact['phone'])->toBe('0501234567')
        ->and($contact['email'])->toBe('hello@example.com')
        ->and($contact['whatsapp'])->toBe('966501234567')
        ->and($contact['country'])->toBe('الإمارات')
        ->and($contact['city'])->toBe('دبي');
});

it('manages social links with ordering', function () {
    $tenant = createTenantForProfile(skipDefaultImport: true);
    $service = app(TenantProfileService::class);

    $service->addSocialLink($tenant, 'twitter', 'https://twitter.com/test');
    $service->addSocialLink($tenant->fresh(), 'instagram', 'https://instagram.com/test');

    $links = $service->socialLinks($tenant->fresh());

    expect($links)->toHaveCount(2)
        ->and($links->first()['network'])->toBe('twitter')
        ->and($links->last()['network'])->toBe('instagram');

    $firstId = $links->first()['id'];
    $secondId = $links->last()['id'];

    $service->updateSocialOrder($tenant->fresh(), [
        ['value' => $firstId, 'order' => 2],
        ['value' => $secondId, 'order' => 1],
    ]);

    $reordered = $service->socialLinks($tenant->fresh());

    expect($reordered->first()['network'])->toBe('instagram')
        ->and($reordered->last()['network'])->toBe('twitter');

    $service->deleteSocialLink($tenant->fresh(), $firstId);

    expect($service->socialLinks($tenant->fresh()))->toHaveCount(1);
});

it('imports contact and social links from the header block when profile is empty', function () {
    $tenant = createTenantForProfile();

    $headerBlock = Block::query()
        ->where('tenant_id', $tenant->id)
        ->where('type', 'header')
        ->firstOrFail();

    $headerBlock->update([
        'data' => [
            'country' => 'السعودية',
            'city' => 'جدة',
        ],
    ]);

    Content::query()
        ->where('block_id', $headerBlock->id)
        ->type('social-link')
        ->delete();

    Content::create([
        'tenant_id' => $tenant->id,
        'block_id' => $headerBlock->id,
        'type' => 'social-link',
        'title' => 'Twitter',
        'slug' => 'twitter-test',
        'data' => [
            'network' => 'twitter',
            'url' => 'https://twitter.com/imported',
        ],
        'sort_order' => 1,
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $tenant->meta->forget('contact');
    $tenant->meta->forget('social_links');
    $tenant->meta->forget('contact_saved');
    $tenant->save();

    $service = app(TenantProfileService::class);
    $contact = $service->contact($tenant->fresh());
    $links = $service->socialLinks($tenant->fresh());

    expect($contact['country'])->toBe('السعودية')
        ->and($contact['city'])->toBe('جدة')
        ->and($contact['email'])->toBe($tenant->user->email)
        ->and($links)->toHaveCount(1)
        ->and($links->first()['url'])->toBe('https://twitter.com/imported');
});

it('does not fall back to owner user contact on public reads', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'member@example.com',
        'phone' => '0509876543',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $tenant->meta->set('contact', [
        'phone' => '',
        'email' => '',
        'whatsapp' => '',
        'country' => '',
        'city' => '',
    ]);
    $tenant->meta->set('social_links', []);
    $tenant->save();

    $contact = app(TenantProfileService::class)->contact($tenant->fresh());

    expect($contact['phone'])->toBe('')
        ->and($contact['email'])->toBe('')
        ->and($tenant->fresh()->relationLoaded('user'))->toBeFalse();
});

it('keeps tenant contact separate from user after saving', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'member@example.com',
        'phone' => '0509876543',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $tenant->meta->set('contact', [
        'phone' => '',
        'email' => '',
        'whatsapp' => '',
        'country' => '',
        'city' => '',
    ]);
    $tenant->meta->set('social_links', []);
    $tenant->save();

    $service = app(TenantProfileService::class);

    $service->saveContact($tenant, [
        'phone' => '0501111111',
        'email' => 'business@example.com',
        'country' => 'السعودية',
        'city' => 'الرياض',
    ]);

    $contact = $service->contact($tenant->fresh());

    expect($contact['phone'])->toBe('0501111111')
        ->and($contact['email'])->toBe('business@example.com')
        ->and($user->fresh()->phone)->toBe('0509876543')
        ->and($user->fresh()->email)->toBe('member@example.com');
});

it('seeds tenant contact from user on registration', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'new@example.com',
        'phone' => '0505555555',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'New Tenant',
        'handle' => 'new-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'active' => true,
        'status' => 'active',
    ]);

    app(TenantProfileService::class)->seedContactFromUser($tenant->fresh());

    $contact = app(TenantProfileService::class)->contact($tenant->fresh());

    expect($contact['email'])->toBe('new@example.com')
        ->and($contact['phone'])->toBe('0505555555');
});

it('does not fall back to owner avatar on public logo reads', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'image' => 'https://example.com/avatar.jpg',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $tenant->meta->set('contact', [
        'phone' => '',
        'email' => '',
        'whatsapp' => '',
        'country' => '',
        'city' => '',
    ]);
    $tenant->meta->set('social_links', []);
    $tenant->save();

    $service = app(TenantProfileService::class);
    $fresh = $tenant->fresh();

    expect($service->logo($fresh))->toBe('https://api.dicebear.com/9.x/shapes/svg?seed='.$fresh->uuid)
        ->and($service->hasLogo($fresh))->toBeFalse()
        ->and($fresh->relationLoaded('user'))->toBeFalse();
});

it('seeds tenant logo from user avatar on registration', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'image' => 'https://example.com/social-avatar.jpg',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'New Tenant',
        'handle' => 'new-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    app(TenantProfileService::class)->seedLogoFromUser($tenant->fresh());

    expect(data_get($tenant->fresh()->meta, 'logo'))->toBe('https://example.com/social-avatar.jpg')
        ->and($tenant->fresh()->logo)->toBe('https://example.com/social-avatar.jpg');
});

it('keeps tenant logo separate from user after uploading custom logo', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'image' => 'https://example.com/avatar.jpg',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $service = app(TenantProfileService::class);
    $service->saveLogo($tenant, 'tenant-media/test/logo.png');

    expect(data_get($tenant->fresh()->meta, 'logo'))->toBe('tenant-media/test/logo.png')
        ->and((bool) data_get($tenant->fresh()->meta, 'logo_saved'))->toBeTrue()
        ->and($user->fresh()->getRawOriginal('image'))->toBe('https://example.com/avatar.jpg');
});
