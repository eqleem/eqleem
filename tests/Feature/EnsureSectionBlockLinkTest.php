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

    Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->where('type', 'block-link')
        ->where('data->link_type', 'section')
        ->delete();

    return [$owner, $tenant];
}

function sectionBlockLinkCount(int $tenantId, string $contentTypeSlug): int
{
    return Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenantId)
        ->where('type', 'block-link')
        ->where('data->content_type', $contentTypeSlug)
        ->where('data->link_type', 'section')
        ->count();
}

dataset('section block link content types', [
    'store' => ['store', 'product', 'المتجر الإلكتروني'],
    'portfolio' => ['portfolio', 'portfolio', 'أعمالنا'],
    'digital-products' => ['digital-products', 'digital-product', 'المنتجات الرقمية'],
    'digital-services' => ['digital-services', 'digital-service', 'الخدمات الرقمية'],
    'services' => ['services', 'service', 'خدماتنا'],
    'newsletter' => ['newsletter', 'newsletter', 'النشرة البريدية'],
    'menu' => ['menu', 'menu', 'قائمة الطعام'],
    'unit-rental' => ['unit-rental', 'unit', 'تأجير الوحدات'],
    'courses' => ['courses', 'course', 'الدورات'],
]);

it('adds a section block link when the first content item is created', function (
    string $contentTypeSlug,
    string $modelType,
    string $expectedTitle,
) {
    [, $tenant] = createTenantForBlockLinkTest();

    $tenant->update([
        'config' => [
            ...($tenant->config ?? []),
            'page_sections_configured' => true,
            'enabled_content_types' => [$contentTypeSlug],
        ],
    ]);

    expect(sectionBlockLinkCount($tenant->id, $contentTypeSlug))->toBe(0);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => $modelType,
        'title' => 'Test Item',
        'slug' => $contentTypeSlug.'-item-'.Str::lower(Str::random(6)),
        'status' => 'draft',
        'active' => true,
    ]);

    expect(sectionBlockLinkCount($tenant->id, $contentTypeSlug))->toBe(1);

    $block = Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->where('type', 'block-link')
        ->where('data->content_type', $contentTypeSlug)
        ->first();

    expect($block)->not->toBeNull()
        ->and($block->position)->toBe('home')
        ->and($block->active)->toBeTrue()
        ->and($block->data['link_type'])->toBe('section')
        ->and($block->data['title'])->toBe($expectedTitle);
})->with('section block link content types');

it('does not duplicate the section block link when another item is created', function (
    string $contentTypeSlug,
    string $modelType,
) {
    [, $tenant] = createTenantForBlockLinkTest();

    $tenant->update([
        'config' => [
            ...($tenant->config ?? []),
            'page_sections_configured' => true,
            'enabled_content_types' => [$contentTypeSlug],
        ],
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => $modelType,
        'title' => 'First Item',
        'slug' => $contentTypeSlug.'-first-'.Str::lower(Str::random(6)),
        'status' => 'draft',
        'active' => true,
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => $modelType,
        'title' => 'Second Item',
        'slug' => $contentTypeSlug.'-second-'.Str::lower(Str::random(6)),
        'status' => 'draft',
        'active' => true,
    ]);

    expect(sectionBlockLinkCount($tenant->id, $contentTypeSlug))->toBe(1);
})->with('section block link content types');

it('does not add a section block link when the section is disabled', function () {
    [, $tenant] = createTenantForBlockLinkTest();

    $tenant->update([
        'config' => [
            'page_sections_configured' => true,
            'enabled_content_types' => [],
        ],
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'Disabled Section Item',
        'slug' => 'disabled-section-item-'.Str::lower(Str::random(6)),
        'status' => 'draft',
        'active' => true,
    ]);

    expect(sectionBlockLinkCount($tenant->id, 'store'))->toBe(0);
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

    expect(sectionBlockLinkCount($tenant->id, 'store'))->toBe(0);
});
