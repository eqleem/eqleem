<?php

use App\Models\RequestAnalytics;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createAnalyticsOverviewUserWithTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'analytics-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الإحصائيات',
        'handle' => 'analytics-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createAnalyticsVisit(Tenant $tenant, array $attributes = []): RequestAnalytics
{
    return RequestAnalytics::withoutGlobalScopes()->create(array_merge([
        'tenant_id' => $tenant->id,
        'path' => '/',
        'ip_address' => '127.0.0.1',
        'browser' => 'Chrome',
        'operating_system' => 'macOS',
        'device' => 'Desktop',
        'referrer' => 'https://google.com/',
        'country' => 'Saudi Arabia',
        'session_id' => (string) Str::uuid(),
        'visitor_id' => (string) Str::uuid(),
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now()->subDays(2),
    ], $attributes));
}

it('returns tenant analytics overview for the authenticated dashboard user', function () {
    [$user, $tenant] = createAnalyticsOverviewUserWithTenant();
    $otherTenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر آخر',
        'handle' => 'other-analytics-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    createAnalyticsVisit($tenant, [
        'path' => '/home',
        'session_id' => 'session-a',
        'visited_at' => now()->subDays(1)->setTime(10, 0),
    ]);
    createAnalyticsVisit($tenant, [
        'path' => '/home',
        'session_id' => 'session-a',
        'visited_at' => now()->subDays(1)->setTime(10, 5),
    ]);
    createAnalyticsVisit($tenant, [
        'path' => '/products',
        'session_id' => 'session-b',
        'browser' => 'Safari',
        'visited_at' => now()->subDays(3),
    ]);

    // Other tenant traffic must not leak into the overview.
    createAnalyticsVisit($otherTenant, [
        'path' => '/secret',
        'session_id' => 'session-other',
        'visited_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/analytics/overview?date_range=30');

    $response->assertSuccessful()
        ->assertJsonPath('data.summary.views', 3)
        ->assertJsonPath('data.summary.visitors', 2)
        ->assertJsonStructure([
            'data' => [
                'summary' => ['views', 'visitors', 'bounce_rate', 'average_visit_time'],
                'chart' => ['labels', 'datasets'],
                'top_pages',
                'top_referrers',
                'browsers',
                'devices',
                'countries',
                'operating_systems',
                'date_range' => ['start', 'end', 'days', 'key'],
            ],
        ]);

    $pages = collect($response->json('data.top_pages'));

    expect($pages->firstWhere('path', '/home')['views'])->toBe(2)
        ->and($pages->contains('path', '/secret'))->toBeFalse();
});

it('requires authentication for analytics overview', function () {
    $this->getJson('/api/analytics/overview')
        ->assertUnauthorized();
});

it('filters analytics overview by request category', function () {
    [$user, $tenant] = createAnalyticsOverviewUserWithTenant();

    createAnalyticsVisit($tenant, [
        'request_category' => 'web',
        'path' => '/web',
    ]);
    createAnalyticsVisit($tenant, [
        'request_category' => 'api',
        'path' => '/api/items',
        'session_id' => 'api-session',
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/analytics/overview?date_range=30&request_category=web');

    $response->assertSuccessful()
        ->assertJsonPath('data.summary.views', 1);

    $pages = collect($response->json('data.top_pages'));

    expect($pages->contains('path', '/web'))->toBeTrue()
        ->and($pages->contains('path', '/api/items'))->toBeFalse();
});
