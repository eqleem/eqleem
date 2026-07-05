<?php

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createTenantForBlockLinkTest(): array
{
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Block Link Tenant',
        'handle' => 'block-link-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return [$owner, $tenant];
}

function storeSectionBlockLinkCount(int $tenantId): int
{
    return Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenantId)
        ->where('type', 'block-link')
        ->where('data->content_type', 'store')
        ->where('data->link_type', 'section')
        ->count();
}

it('adds a store block link when the first product is created', function () {
    [, $tenant] = createTenantForBlockLinkTest();

    expect(storeSectionBlockLinkCount($tenant->id))->toBe(0);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'Product One',
        'slug' => 'product-one',
        'status' => 'draft',
        'active' => true,
    ]);

    expect(storeSectionBlockLinkCount($tenant->id))->toBe(1);

    $block = Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->where('type', 'block-link')
        ->where('data->content_type', 'store')
        ->first();

    expect($block)->not->toBeNull()
        ->and($block->position)->toBe('home')
        ->and($block->active)->toBeTrue()
        ->and($block->data['link_type'])->toBe('section')
        ->and($block->data['title'])->toBe('المتجر الإلكتروني');
});

it('does not duplicate the store block link when another product is created', function () {
    [, $tenant] = createTenantForBlockLinkTest();

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'Product One',
        'slug' => 'product-one',
        'status' => 'draft',
        'active' => true,
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'Product Two',
        'slug' => 'product-two',
        'status' => 'draft',
        'active' => true,
    ]);

    expect(storeSectionBlockLinkCount($tenant->id))->toBe(1);
});

it('does not add a block link for excluded content types like forms', function () {
    [, $tenant] = createTenantForBlockLinkTest();

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Contact Form',
        'slug' => 'contact-form',
        'status' => 'published',
        'active' => true,
    ]);

    expect(Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->where('type', 'block-link')
        ->where('data->content_type', 'forms')
        ->count())->toBe(0);
});

it('does not add a block link for nested block content', function () {
    [, $tenant] = createTenantForBlockLinkTest();

    $headerBlock = Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->where('type', 'header')
        ->firstOrFail();

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'block_id' => $headerBlock->id,
        'type' => 'social-link',
        'title' => 'Twitter',
        'slug' => 'twitter-link-'.Str::lower(Str::random(6)),
        'status' => 'published',
        'active' => true,
    ]);

    expect(storeSectionBlockLinkCount($tenant->id))->toBe(0);
});
