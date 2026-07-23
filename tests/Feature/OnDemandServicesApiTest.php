<?php

use App\Models\Content;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ContentTypeRegistry;
use App\Support\OnDemandUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForOnDemandServices(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'خدمات حسب الطلب',
        'handle' => 'ondemand-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access on-demand services endpoints', function () {
    $this->getJson('/api/on-demand-services')->assertUnauthorized();
    $this->postJson('/api/on-demand-services', ['title' => 'خدمة'])->assertUnauthorized();
});

test('owner can create list update and delete on-demand services with unit pricing', function () {
    [$user, $tenant] = createUserWithTenantForOnDemandServices();

    $create = $this->actingAs($user)
        ->postJson('/api/on-demand-services', ['title' => 'تركيب أرضيات'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'تركيب أرضيات')
        ->assertJsonPath('data.unit_type', OnDemandUnit::SquareMeter);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->putJson("/api/on-demand-services/{$uuid}", [
            'title' => 'تركيب أرضيات خشبية',
            'subtitle' => 'حسب المتر المربع',
            'body' => '<p>تفاصيل التركيب</p>',
            'slug' => 'flooring-install',
            'price' => 20,
            'compare_price' => 25,
            'unit_type' => OnDemandUnit::SquareMeter,
            'unit_label' => '',
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'تركيب أرضيات خشبية')
        ->assertJsonPath('data.unit_type', OnDemandUnit::SquareMeter)
        ->assertJsonPath('data.unit_display', 'متر مربع')
        ->assertJsonPath('data.price', '20')
        ->assertJsonPath('data.compare_price', '25')
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true);

    setCurrentTenant($tenant);

    $service = Content::query()->where('uuid', $uuid)->first();

    expect($service)->not->toBeNull()
        ->and(data_get($service->data, 'unit_type'))->toBe(OnDemandUnit::SquareMeter)
        ->and(data_get($service->data, 'price'))->toBe(money_minor(20));

    $list = $this->actingAs($user)
        ->getJson('/api/on-demand-services')
        ->assertSuccessful();

    expect($list->json('data.0.price_label'))->toBe(money_format_plain(money_minor(20)).' / متر مربع');

    $this->actingAs($user)
        ->putJson("/api/on-demand-services/{$uuid}", [
            'title' => 'طباعة مخصصة',
            'subtitle' => '',
            'body' => '',
            'slug' => 'custom-print',
            'price' => 15,
            'unit_type' => OnDemandUnit::Other,
            'unit_label' => 'متر طولي',
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.unit_type', OnDemandUnit::Other)
        ->assertJsonPath('data.unit_label', 'متر طولي')
        ->assertJsonPath('data.unit_display', 'متر طولي');

    $this->actingAs($user)
        ->deleteJson('/api/on-demand-services', ['ids' => [$service->id]])
        ->assertSuccessful();
});

test('unit label is required when unit type is other', function () {
    [$user] = createUserWithTenantForOnDemandServices();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/on-demand-services', ['title' => 'خدمة أخرى'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->putJson("/api/on-demand-services/{$uuid}", [
            'title' => 'خدمة أخرى',
            'slug' => 'other-service',
            'price' => 10,
            'unit_type' => OnDemandUnit::Other,
            'unit_label' => '',
            'active' => false,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unit_label']);
});

test('owner can get and update on-demand service settings', function () {
    [$user] = createUserWithTenantForOnDemandServices();

    $this->actingAs($user)
        ->getJson('/api/on-demand-services/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'خدمات حسب الطلب');

    $this->actingAs($user)
        ->putJson('/api/on-demand-services/settings', [
            'section_title' => 'خدماتنا حسب الطلب',
            'section_description' => 'تسعير واضح لكل وحدة',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'خدماتنا حسب الطلب');

    expect(Setting::onDemandServiceSettings()['section_title'])->toBe('خدماتنا حسب الطلب');
});

test('owner can upload on-demand service gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForOnDemandServices();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/on-demand-services', ['title' => 'خدمة صور'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/on-demand-services/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('service.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});

test('on-demand services are configured as an active sellable content type', function () {
    $type = app(ContentTypeRegistry::class)->configured()
        ->firstWhere('slug', 'on-demand-services');

    expect($type)->not->toBeNull()
        ->and($type->sellable)->toBeTrue()
        ->and($type->active)->toBeTrue()
        ->and($type->name)->toBe('خدمة حسب الطلب')
        ->and($type->modelType)->toBe('on-demand-service');

    $catalogOptions = app(ContentTypeRegistry::class)->configured()
        ->filter(fn ($contentType): bool => $contentType->sellable)
        ->pluck('slug')
        ->all();

    expect($catalogOptions)->toContain('on-demand-services');
});

test('on-demand service detail active state stays in sync after table toggle and detail update', function () {
    [$user] = createUserWithTenantForOnDemandServices();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/on-demand-services', ['title' => 'خدمة مزامنة'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->putJson("/api/on-demand-services/{$uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    $this->actingAs($user)
        ->getJson("/api/on-demand-services/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false);

    $this->actingAs($user)
        ->putJson("/api/on-demand-services/{$uuid}", [
            'title' => 'خدمة مزامنة',
            'slug' => 'sync-on-demand',
            'unit_type' => OnDemandUnit::SquareMeter,
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true);

    $this->actingAs($user)
        ->getJson('/api/on-demand-services')
        ->assertSuccessful()
        ->assertJsonPath('data.0.active', true);
});
