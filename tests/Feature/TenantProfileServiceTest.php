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
        ->and($contact['city'])->toBe('الرياض');
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
    $tenant->save();

    $service = app(TenantProfileService::class);
    $contact = $service->contact($tenant->fresh());
    $links = $service->socialLinks($tenant->fresh());

    expect($contact['country'])->toBe('السعودية')
        ->and($contact['city'])->toBe('جدة')
        ->and($links)->toHaveCount(1)
        ->and($links->first()['url'])->toBe('https://twitter.com/imported');
});
