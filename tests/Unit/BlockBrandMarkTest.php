<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\BlockBrandMark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createTenantForBlockBrandMark(): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    return Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر',
        'handle' => 'mark-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);
}

test('block brand mark resolves emoji and icon for editor', function () {
    expect(BlockBrandMark::forEditor([
        'type' => 'emoji',
        'value' => '🚀',
        'color' => '',
    ]))->toMatchArray([
        'type' => 'emoji',
        'value' => '🚀',
        'color' => '',
        'url' => null,
    ]);

    expect(BlockBrandMark::forEditor([
        'type' => 'icon',
        'value' => 'tabler:home',
        'color' => '#DC2626',
    ]))->toMatchArray([
        'type' => 'icon',
        'value' => 'tabler:home',
        'color' => '#dc2626',
        'url' => null,
    ]);
});

test('block brand mark normalizes empty color as inherit and accepts black', function () {
    expect(BlockBrandMark::normalizeColor(''))->toBe('')
        ->and(BlockBrandMark::normalizeColor('inherit'))->toBe('')
        ->and(BlockBrandMark::normalizeColor('currentColor'))->toBe('')
        ->and(BlockBrandMark::normalizeColor('#000'))->toBe('#000000')
        ->and(BlockBrandMark::normalizeColor('#000000'))->toBe('#000000')
        ->and(BlockBrandMark::normalizeColor('not-a-color'))->toBe('');

    expect(BlockBrandMark::forEditor([
        'type' => 'icon',
        'value' => 'tabler:star',
        'color' => '',
    ]))->toMatchArray([
        'type' => 'icon',
        'value' => 'tabler:star',
        'color' => '',
        'url' => null,
    ]);
});

test('block brand mark stores uploaded image on spaces without touching tenant meta', function () {
    Storage::fake('spaces');

    $tenant = createTenantForBlockBrandMark();

    $stored = BlockBrandMark::resolveStored($tenant, 42, [
        'brand_mark_type' => 'image',
        'logo' => UploadedFile::fake()->image('mark.jpg', 120, 120),
    ], null);

    expect($stored)->toMatchArray([
        'type' => 'image',
        'value' => '',
        'color' => '',
    ])->and($stored['path'] ?? null)->toContain('block-links/42');

    expect(BlockBrandMark::forEditor($stored)['url'])->not->toBeEmpty()
        ->and(data_get($tenant->fresh()->meta, 'brand_mark'))->toBeNull()
        ->and(data_get($tenant->fresh()->meta, 'logo'))->toBeNull();
});

test('block brand mark clears and preserves existing values', function () {
    $tenant = createTenantForBlockBrandMark();

    $existing = [
        'type' => 'emoji',
        'value' => '🔥',
        'color' => '',
    ];

    expect(BlockBrandMark::resolveStored($tenant, 1, [
        'brand_mark_type' => 'none',
        'remove_logo' => true,
    ], $existing))->toBeNull();

    expect(BlockBrandMark::resolveStored($tenant, 1, [
        'link_type' => 'section:blog',
    ], $existing))->toMatchArray($existing);
});
