<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createTenantWithOwner(): array
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

    return [$owner, $tenant];
}

it('assigns the authenticated user when creating content', function () {
    [$owner, $tenant] = createTenantWithOwner();

    $this->actingAs($owner);

    $content = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('blog'),
        'title' => 'Test Post',
        'slug' => 'test-post',
        'status' => 'draft',
        'active' => true,
    ]);

    expect($content->fresh()->user_id)->toBe($owner->id);
});

it('does not override an explicitly provided user id', function () {
    [$owner, $tenant] = createTenantWithOwner();

    $otherUser = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $this->actingAs($owner);

    $content = Content::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $otherUser->id,
        'type' => contentTypeModel('blog'),
        'title' => 'Assigned Post',
        'slug' => 'assigned-post',
        'status' => 'draft',
        'active' => true,
    ]);

    expect($content->fresh()->user_id)->toBe($otherUser->id);
});

it('falls back to the tenant owner when creating content without authentication', function () {
    [$owner, $tenant] = createTenantWithOwner();

    $content = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Seeded Form',
        'slug' => 'seeded-form',
        'status' => 'published',
        'active' => true,
    ]);

    expect($content->fresh()->user_id)->toBe($owner->id);
});
