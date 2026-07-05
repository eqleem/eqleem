<?php

use App\Models\Client;
use App\Models\RequestAnalytics;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use SaKanjo\EasyMetrics\Metrics\Value;

uses(RefreshDatabase::class);

function createTenantForRequestAnalytics(string $handle): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    return Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant '.$handle,
        'handle' => $handle,
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createRequestAnalyticsRecord(Tenant $tenant, array $attributes = []): RequestAnalytics
{
    return RequestAnalytics::withoutGlobalScopes()->create(array_merge([
        'tenant_id' => $tenant->id,
        'path' => '/',
        'ip_address' => '127.0.0.1',
        'session_id' => (string) Str::uuid(),
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ], $attributes));
}

it('scopes request analytics to the current tenant', function () {
    $tenantA = createTenantForRequestAnalytics('tenant-a-'.Str::lower(Str::random(6)));
    $tenantB = createTenantForRequestAnalytics('tenant-b-'.Str::lower(Str::random(6)));

    createRequestAnalyticsRecord($tenantA);
    createRequestAnalyticsRecord($tenantA);
    createRequestAnalyticsRecord($tenantB);

    setCurrentTenant($tenantA);

    expect(RequestAnalytics::count())->toBe(2);
});

it('counts tenant visits using visited_at via easy metrics', function () {
    $tenant = createTenantForRequestAnalytics('tenant-metrics-'.Str::lower(Str::random(6)));

    createRequestAnalyticsRecord($tenant, [
        'visited_at' => now()->subDays(2),
    ]);

    createRequestAnalyticsRecord($tenant, [
        'visited_at' => now()->subDays(10),
    ]);

    createRequestAnalyticsRecord($tenant, [
        'visited_at' => now()->subDays(20),
    ]);

    setCurrentTenant($tenant);

    $count = Value::make(RequestAnalytics::class)
        ->dateColumn('visited_at')
        ->range(15)
        ->count();

    expect($count)->toBe(2.0);
});

it('assigns tenant_id when package model is created', function () {
    $tenant = createTenantForRequestAnalytics('tenant-observer-'.Str::lower(Str::random(6)));

    setCurrentTenant($tenant);

    $record = MeShaon\RequestAnalytics\Models\RequestAnalytics::create([
        'path' => '/about',
        'ip_address' => '127.0.0.1',
        'session_id' => (string) Str::uuid(),
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ]);

    expect($record->tenant_id)->toBe($tenant->id);
});

it('assigns client_id when a client is authenticated', function () {
    $tenant = createTenantForRequestAnalytics('tenant-client-'.Str::lower(Str::random(6)));

    $client = Client::withoutGlobalScope('tenantable')->create([
        'name' => 'Analytics Client',
        'email' => 'analytics-client@example.com',
        'tenant_id' => $tenant->id,
    ]);

    setCurrentTenant($tenant);
    $this->actingAs($client, 'client');

    $record = MeShaon\RequestAnalytics\Models\RequestAnalytics::create([
        'path' => '/shop',
        'ip_address' => '127.0.0.1',
        'session_id' => (string) Str::uuid(),
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ]);

    expect($record->client_id)->toBe($client->id)
        ->and($record->tenant_id)->toBe($tenant->id);
});
