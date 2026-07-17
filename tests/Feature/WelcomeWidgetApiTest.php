<?php

use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createWelcomeWidgetUserWithTenant(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد',
        'email' => 'welcome-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'welcome-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

it('returns welcome widget payload for the current tenant', function () {
    [$user, $tenant] = createWelcomeWidgetUserWithTenant();

    $this->actingAs($user)
        ->getJson('/api/dashboard/welcome')
        ->assertSuccessful()
        ->assertJsonPath('data.user_name', 'أحمد')
        ->assertJsonPath('data.completed_steps', fn ($value) => is_int($value))
        ->assertJsonPath('data.total_steps', 5)
        ->assertJsonStructure([
            'data' => [
                'greeting',
                'user_name',
                'page_url',
                'share_text',
                'percentage',
                'completed_steps',
                'total_steps',
                'steps',
                'next_step',
                'forms' => [
                    'basic_info' => ['name', 'bio', 'logo'],
                    'contact' => ['phone', 'email', 'country', 'city'],
                    'social_networks',
                ],
            ],
        ]);

    expect($tenant->handle)->not->toBeEmpty();
});

it('updates welcome basic info and refreshes completion', function () {
    [$user] = createWelcomeWidgetUserWithTenant();

    setCurrentTenant($user->currentTenant);
    $headerBlock = Block::findSingleton('header');
    expect($headerBlock)->not->toBeNull();

    Storage::fake('spaces');

    $logo = UploadedFile::fake()->image('logo.png', 120, 120);

    $before = $this->actingAs($user)
        ->getJson('/api/dashboard/welcome')
        ->assertSuccessful()
        ->json('data.completed_steps');

    $this->actingAs($user)
        ->post('/api/dashboard/welcome/basic-info', [
            'name' => 'صفحة محدثة',
            'bio' => 'نبذة قصيرة',
            'logo' => $logo,
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.basic_info.name', 'صفحة محدثة')
        ->assertJsonPath('data.forms.basic_info.bio', 'نبذة قصيرة')
        ->assertJsonPath('message', __('Settings updated successfully.'));

    expect($user->currentTenant->fresh()->name)->toBe('صفحة محدثة');
    expect(data_get($user->currentTenant->fresh()->meta, 'bio'))->toBe('نبذة قصيرة');
    expect(app(TenantProfileService::class)->hasLogo($user->currentTenant->fresh()))->toBeTrue();

    $after = $this->actingAs($user)
        ->getJson('/api/dashboard/welcome')
        ->json('data');

    expect($after['completed_steps'])->toBeGreaterThan($before);
    expect(collect($after['steps'])->firstWhere('key', 'basic-info')['done'])->toBeTrue();
    expect($after['next_step']['key'] ?? null)->not->toBe('basic-info');
});

it('updates welcome contact details', function () {
    [$user] = createWelcomeWidgetUserWithTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/welcome/contact', [
            'phone' => '0501234567',
            'email' => 'hello@example.com',
            'country' => 'السعودية',
            'city' => 'الرياض',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.contact.phone', '0501234567')
        ->assertJsonPath('data.forms.contact.city', 'الرياض');

    $contact = app(TenantProfileService::class)->contact($user->currentTenant->fresh());

    expect($contact['email'])->toBe('hello@example.com');
    expect($contact['country'])->toBe('السعودية');
});

it('adds a welcome social link', function () {
    [$user] = createWelcomeWidgetUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/dashboard/welcome/social', [
            'network' => 'instagram',
            'url' => 'https://instagram.com/mystore',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'تمت إضافة رابط التواصل بنجاح');

    $links = app(TenantProfileService::class)->socialLinks($user->currentTenant->fresh());

    expect($links->contains(fn (array $link): bool => $link['network'] === 'instagram' && $link['url'] === 'https://instagram.com/mystore'))->toBeTrue();
});

it('rejects unauthenticated welcome requests', function () {
    $this->getJson('/api/dashboard/welcome')->assertUnauthorized();
});
