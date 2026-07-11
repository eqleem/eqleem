<?php

use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForMenu(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'مطعم تجريبي',
        'handle' => 'menu-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access menu endpoints', function () {
    $this->getJson('/api/menu')->assertUnauthorized();
    $this->postJson('/api/menu', ['title' => 'طبق'])->assertUnauthorized();
    $this->getJson('/api/menu/categories')->assertUnauthorized();
    $this->getJson('/api/menu/settings')->assertUnauthorized();
});

test('owner can create list update and delete menu items', function () {
    [$user, $tenant] = createUserWithTenantForMenu();

    $create = $this->actingAs($user)
        ->postJson('/api/menu', ['title' => 'برجر لحم'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'برجر لحم')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/menu')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/menu/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'برجر لحم')
        ->assertJsonStructure([
            'data' => [
                'category_options',
                'images',
                'slug_prefix',
                'price',
                'compare_price',
                'meal_options',
            ],
        ]);

    setCurrentTenant($tenant);

    $parent = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'وجبات',
        'type' => 'menu_category',
        'sort_order' => 0,
    ]);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'برجر',
        'type' => 'menu_category',
        'parent_id' => $parent->id,
        'sort_order' => 0,
    ]);

    $groupId = (string) Str::uuid();
    $choiceId = (string) Str::uuid();

    $this->actingAs($user)
        ->putJson("/api/menu/{$uuid}", [
            'title' => 'برجر لحم محدث',
            'slug' => 'beef-burger',
            'price' => 45.00,
            'compare_price' => 55,
            'category_ids' => [$leaf->id],
            'published' => true,
            'meal_options' => [
                [
                    'id' => $groupId,
                    'name' => 'حجم الوجبة',
                    'type' => 'single',
                    'required' => true,
                    'choices' => [
                        [
                            'id' => $choiceId,
                            'name' => 'كبير',
                            'price' => 5,
                        ],
                    ],
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'برجر لحم محدث')
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.price', '45')
        ->assertJsonPath('data.compare_price', '55')
        ->assertJsonPath('data.category_ids.0', (string) $leaf->id)
        ->assertJsonPath('data.meal_options.0.name', 'حجم الوجبة')
        ->assertJsonPath('data.meal_options.0.type', 'single')
        ->assertJsonPath('data.meal_options.0.required', true)
        ->assertJsonPath('data.meal_options.0.choices.0.name', 'كبير')
        ->assertJsonPath('data.meal_options.0.choices.0.price', '5');

    setCurrentTenant($tenant);

    $item = Content::query()->where('uuid', $uuid)->first();

    expect($item)->not->toBeNull()
        ->and($item->status)->toBe('published')
        ->and(data_get($item->data, 'price'))->toBe(money_minor(45.00))
        ->and(data_get($item->data, 'meal_options.0.name'))->toBe('حجم الوجبة')
        ->and(data_get($item->data, 'meal_options.0.choices.0.price'))->toBe(money_minor(5));

    $this->actingAs($user)
        ->deleteJson('/api/menu', ['ids' => [$item->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);

    setCurrentTenant($tenant);

    expect(Content::query()->where('uuid', $uuid)->exists())->toBeFalse();
});

test('owner can manage menu categories and reorder them', function () {
    [$user, $tenant] = createUserWithTenantForMenu();

    $first = $this->actingAs($user)
        ->postJson('/api/menu/categories', ['name' => 'مقبلات'])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'مقبلات');

    $firstId = (int) $first->json('data.category.id');

    $this->actingAs($user)
        ->deleteJson("/api/menu/categories/{$firstId}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($firstId)->exists())->toBeFalse();
});

test('owner can get and update menu settings', function () {
    [$user] = createUserWithTenantForMenu();

    $this->actingAs($user)
        ->getJson('/api/menu/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'قائمة الطعام');

    $this->actingAs($user)
        ->putJson('/api/menu/settings', [
            'section_title' => 'قائمتنا',
            'section_description' => 'أشهى الأطباق',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'قائمتنا');

    expect(Setting::menuSettings()['section_title'])->toBe('قائمتنا');
});

test('owner can upload menu gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForMenu();

    $create = $this->actingAs($user)
        ->postJson('/api/menu', ['title' => 'طبق صور'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/menu/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('dish.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});
