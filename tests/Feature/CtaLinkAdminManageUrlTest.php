<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CtaLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('builds admin manage url for section block links', function () {
    $url = CtaLink::adminManageUrlFromData([
        'link_type' => 'section',
        'content_type' => 'blog',
    ]);

    expect($url)->toBe(route('admin.page.home', ['tab' => 'content-blog']));
});

it('builds admin manage url for item block links', function () {
    $owner = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Cta Link Tenant',
        'handle' => 'cta-link-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $content = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('blog'),
        'title' => 'Test Post',
        'slug' => 'test-post',
        'status' => 'published',
        'active' => true,
    ]);

    $url = CtaLink::adminManageUrlFromData([
        'link_type' => 'item',
        'content_type' => 'blog',
        'content_id' => $content->id,
    ]);

    expect($url)->toBe(route('admin.page.home', [
        'tab' => 'content-blog',
        'item' => $content->uuid,
    ]));
});

it('returns null for unsupported block link data', function () {
    expect(CtaLink::adminManageUrlFromData([
        'link_type' => 'external',
        'url' => 'https://example.com',
    ]))->toBeNull()
        ->and(CtaLink::adminManageUrlFromData([
            'link_type' => 'section',
            'content_type' => 'unknown-type',
        ]))->toBeNull()
        ->and(CtaLink::adminManageUrlFromData([
            'link_type' => 'item',
            'content_type' => 'blog',
            'content_id' => 99999,
        ]))->toBeNull();
});
