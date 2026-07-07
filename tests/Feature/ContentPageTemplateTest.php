<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createTenantForPageTemplateTest(): Tenant
{
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return $tenant;
}

it('stores a page template on content', function () {
    $tenant = createTenantForPageTemplateTest();

    $content = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('pages'),
        'template' => 'features',
        'title' => 'Features',
        'slug' => 'features-page',
        'status' => 'published',
        'active' => true,
    ]);

    expect($content->fresh()->template)->toBe('features')
        ->and(Content::query()->template('features')->whereKey($content->id)->exists())->toBeTrue();
});

it('exposes page template options', function () {
    expect(Content::pageTemplateOptions())->toHaveKeys(['default', 'contact', 'faq']);
});

it('prevents deleting system pages', function () {
    [, $tenant] = (function (): array {
        $user = User::factory()->create([
            'uuid' => (string) Str::uuid(),
        ]);

        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Seed Defaults Tenant',
            'handle' => 'seed-defaults-'.Str::lower(Str::random(6)),
            'user_id' => $user->id,
            'active' => true,
            'status' => 'active',
        ]);

        $user->update(['current_tenant_id' => $tenant->id]);
        setCurrentTenant($tenant);

        CreateDefaultBlocks::run($tenant);

        return [$user, $tenant];
    })();

    $page = Content::query()
        ->type(contentTypeModel('pages'))
        ->where('template', 'faq')
        ->firstOrFail();

    expect($page->isSystemPage())->toBeTrue()
        ->and($page->delete())->toBeFalse()
        ->and(Content::query()->whereKey($page->id)->exists())->toBeTrue();
});
