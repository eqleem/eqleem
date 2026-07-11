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
function createUserWithTenantForDigitalServices(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'خدمات رقمية',
        'handle' => 'digital-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access digital services endpoints', function () {
    $this->getJson('/api/digital-services')->assertUnauthorized();
    $this->postJson('/api/digital-services', ['title' => 'خدمة'])->assertUnauthorized();
    $this->getJson('/api/digital-services/categories')->assertUnauthorized();
});

test('owner can create list update and delete digital services', function () {
    [$user, $tenant] = createUserWithTenantForDigitalServices();

    $create = $this->actingAs($user)
        ->postJson('/api/digital-services', ['title' => 'تصميم شعار'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'تصميم شعار');

    $uuid = (string) $create->json('data.uuid');

    setCurrentTenant($tenant);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تصميم',
        'type' => 'digital_service_category',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/digital-services/{$uuid}", [
            'title' => 'تصميم شعار محدث',
            'subtitle' => 'شعار احترافي',
            'body' => '<p>تفاصيل</p>',
            'slug' => 'logo-design',
            'price' => 299,
            'compare_price' => 399,
            'delivery_days' => 3,
            'category_ids' => [$leaf->id],
            'published' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'تصميم شعار محدث')
        ->assertJsonPath('data.subtitle', 'شعار احترافي')
        ->assertJsonPath('data.delivery_days', '3')
        ->assertJsonPath('data.compare_price', '399');

    setCurrentTenant($tenant);

    $service = Content::query()->where('uuid', $uuid)->first();

    expect($service)->not->toBeNull()
        ->and(data_get($service->data, 'delivery_days'))->toBe(3);

    $this->actingAs($user)
        ->deleteJson('/api/digital-services', ['ids' => [$service->id]])
        ->assertSuccessful();
});

test('owner can get and update digital service settings', function () {
    [$user] = createUserWithTenantForDigitalServices();

    $this->actingAs($user)
        ->getJson('/api/digital-services/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'الخدمات الرقمية');

    $this->actingAs($user)
        ->putJson('/api/digital-services/settings', [
            'section_title' => 'خدماتنا الرقمية',
            'section_description' => 'تسليم سريع',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'خدماتنا الرقمية');

    expect(Setting::digitalServiceSettings()['section_title'])->toBe('خدماتنا الرقمية');
});

test('owner can upload digital service gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForDigitalServices();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/digital-services', ['title' => 'خدمة صور'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/digital-services/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('digital.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});
