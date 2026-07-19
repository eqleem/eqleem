<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Content;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createTenantForSectionContentCounts(): array
{
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الأعداد',
        'handle' => 'section-counts-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

function createCountedSectionBlock(Tenant $tenant, string $contentType, int $order): Block
{
    return Block::query()->create([
        'tenant_id' => $tenant->id,
        'component' => 'tenant::components.block-link',
        'type' => 'block-link',
        'title' => $contentType,
        'sort_order' => $order,
        'is_default' => false,
        'status' => 'draft',
        'active' => true,
        'position' => 'home',
        'data' => [
            'link_type' => 'section',
            'content_type' => $contentType,
        ],
    ]);
}

function createCountedContent(Tenant $tenant, string $type, bool $active = true, ?int $parentId = null): Content
{
    return Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => $type,
        'title' => Str::headline($type).'-'.Str::random(5),
        'slug' => $type.'-'.Str::lower(Str::random(8)),
        'active' => $active,
        'status' => 'draft',
        'parent_id' => $parentId,
    ]);
}

test('guests cannot load section content counts', function () {
    $this->getJson('/api/page/section-content-counts')->assertUnauthorized();
});

test('section content counts load active root content with model type aliases', function () {
    [$user, $tenant] = createTenantForSectionContentCounts();

    $blogBlock = createCountedSectionBlock($tenant, 'blog', 90);
    $storeBlock = createCountedSectionBlock($tenant, 'store', 91);

    $externalBlock = Block::query()->create([
        'tenant_id' => $tenant->id,
        'component' => 'tenant::components.block-link',
        'type' => 'block-link',
        'title' => 'رابط خارجي',
        'sort_order' => 92,
        'is_default' => false,
        'status' => 'draft',
        'active' => true,
        'position' => 'home',
        'data' => [
            'link_type' => 'external',
            'url' => 'https://example.com',
        ],
    ]);

    $parent = createCountedContent($tenant, 'blog');
    createCountedContent($tenant, 'blog');
    createCountedContent($tenant, 'blog', false);
    createCountedContent($tenant, 'blog', true, $parent->id);
    createCountedContent($tenant, 'product');
    $deleted = createCountedContent($tenant, 'product');
    $deleted->delete();

    $this->actingAs($user)
        ->getJson('/api/page/section-content-counts')
        ->assertSuccessful()
        ->assertJsonPath('data.'.$blogBlock->id.'.count', 2)
        ->assertJsonPath('data.'.$blogBlock->id.'.label', 'تدوينات')
        ->assertJsonPath('data.'.$storeBlock->id.'.count', 1)
        ->assertJsonPath('data.'.$storeBlock->id.'.label', 'منتج')
        ->assertJsonMissingPath('data.'.$externalBlock->id);
});

test('section content counts are tenant isolated and include zero values', function () {
    [$user, $tenant] = createTenantForSectionContentCounts();
    $blogBlock = createCountedSectionBlock($tenant, 'blog', 90);
    $coursesBlock = createCountedSectionBlock($tenant, 'courses', 91);

    $otherUser = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $otherTenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر آخر',
        'handle' => 'other-counts-'.Str::lower(Str::random(6)),
        'user_id' => $otherUser->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($otherTenant);
    createCountedContent($otherTenant, 'blog');
    setCurrentTenant($tenant);

    $this->actingAs($user)
        ->getJson('/api/page/section-content-counts')
        ->assertSuccessful()
        ->assertJsonPath('data.'.$blogBlock->id.'.count', 0)
        ->assertJsonPath('data.'.$blogBlock->id.'.label', 'تدوينات')
        ->assertJsonPath('data.'.$coursesBlock->id.'.count', 0)
        ->assertJsonPath('data.'.$coursesBlock->id.'.label', 'دورات');
});

test('reviews section content counts use the reviews table', function () {
    [$user, $tenant] = createTenantForSectionContentCounts();
    $reviewsBlock = createCountedSectionBlock($tenant, 'reviews', 93);
    $content = createCountedContent($tenant, 'product');

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $content->id,
        'name' => 'زائر',
        'rating' => 5,
        'published' => true,
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $content->id,
        'name' => 'عميل',
        'rating' => 4,
        'published' => false,
    ]);

    $this->actingAs($user)
        ->getJson('/api/page/section-content-counts')
        ->assertSuccessful()
        ->assertJsonPath('data.'.$reviewsBlock->id.'.count', 2)
        ->assertJsonPath('data.'.$reviewsBlock->id.'.label', 'تقييمات');
});
