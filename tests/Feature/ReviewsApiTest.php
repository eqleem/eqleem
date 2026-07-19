<?php

use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createReviewsApiTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'reviews-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر التقييمات',
        'handle' => 'reviews-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant];
}

function createReviewsApiContent(Tenant $tenant, string $title): Content
{
    setCurrentTenant($tenant);

    return Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => $title,
        'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
        'status' => 'published',
        'active' => true,
    ]);
}

test('guests cannot list reviews', function () {
    $this->getJson('/api/reviews')->assertUnauthorized();
});

test('owner lists current tenant reviews with registered and guest reviewer data', function () {
    [$user, $tenant] = createReviewsApiTenant();
    $content = createReviewsApiContent($tenant, 'منتج مميز');

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'name' => 'عميل مسجل',
        'email' => 'client@example.com',
        'phone' => '0500000001',
        'active' => true,
    ]);
    $client->tenants()->attach($tenant->id, ['active' => true]);

    $order = Order::query()->create([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => 'order',
        'status' => 'completed',
        'channel' => 'ecommerce',
        'number' => 'ORD-1042',
        'currency_code' => 'SAR',
        'payment_status' => 'paid',
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $content->id,
        'client_id' => $client->id,
        'order_id' => $order->id,
        'title' => 'تجربة ممتازة',
        'score' => 'الخدمة سريعة والمنتج ممتاز.',
        'rating' => 5,
        'published' => true,
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $content->id,
        'name' => 'زائر',
        'email' => 'guest@example.com',
        'phone' => '0500000002',
        'rating' => 4,
    ]);

    [, $otherTenant] = createReviewsApiTenant();
    $otherContent = createReviewsApiContent($otherTenant, 'منتج مستأجر آخر');

    Review::query()->create([
        'tenant_id' => $otherTenant->id,
        'content_id' => $otherContent->id,
        'name' => 'مراجع آخر',
        'rating' => 1,
    ]);

    setCurrentTenant($tenant);

    $this->actingAs($user)
        ->getJson('/api/reviews')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'title' => 'تجربة ممتازة',
            'registered' => true,
            'name' => 'عميل مسجل',
            'number' => 'ORD-1042',
        ])
        ->assertJsonFragment([
            'registered' => false,
            'name' => 'زائر',
            'email' => 'guest@example.com',
        ])
        ->assertJsonMissing(['name' => 'مراجع آخر']);
});

test('reviews can be searched by order number', function () {
    [$user, $tenant] = createReviewsApiTenant();
    $content = createReviewsApiContent($tenant, 'منتج قابل للتقييم');

    $order = Order::query()->create([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => 'order',
        'status' => 'completed',
        'channel' => 'ecommerce',
        'number' => 'SEARCH-900',
        'currency_code' => 'SAR',
        'payment_status' => 'paid',
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $content->id,
        'order_id' => $order->id,
        'name' => 'صاحب الطلب',
        'rating' => 5,
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $content->id,
        'name' => 'تقييم آخر',
        'rating' => 3,
    ]);

    $this->actingAs($user)
        ->getJson('/api/reviews?search=SEARCH-900')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.order.number', 'SEARCH-900');
});
