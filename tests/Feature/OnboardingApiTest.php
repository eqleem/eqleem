<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Theme}
 */
function createOnboardingUserWithTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد',
    ]);

    $theme = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إفتراضي',
        'slug' => 'default',
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'onboard-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh(), $theme];
}

it('returns onboarding payload for the current tenant', function () {
    [$user] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->getJson('/api/dashboard/onboarding')
        ->assertSuccessful()
        ->assertJsonPath('data.total_steps', 6)
        ->assertJsonPath('data.completed', false)
        ->assertJsonPath('data.dismissed', false)
        ->assertJsonPath('data.current_step', 'business')
        ->assertJsonStructure([
            'data' => [
                'percentage',
                'completed_steps',
                'total_steps',
                'current_step',
                'completed',
                'dismissed',
                'page_url',
                'steps',
                'forms' => [
                    'business' => ['industry', 'name', 'bio', 'logo', 'brand_mark'],
                    'contact' => ['phone', 'email', 'whatsapp', 'country', 'city', 'social_links'],
                    'identity' => ['theme_id', 'handle', 'primary_color', 'header_image'],
                    'goal' => ['primary_action_type', 'secondary_action_type'],
                    'catalog' => ['enabled'],
                    'orders' => ['payment_active', 'shipping_active', 'verification_done'],
                ],
                'industries',
                'industry_options',
                'action_options',
                'catalog_options',
            ],
        ]);
});

it('saves business step with industry name bio and logo', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();

    Storage::fake('spaces');

    $this->actingAs($user)
        ->post('/api/dashboard/onboarding/business', [
            'industry' => 'retail',
            'name' => 'متجر أحمد',
            'bio' => 'نبيع منتجات مميزة',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
            'brand_mark_type' => 'image',
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.business.industry', 'retail')
        ->assertJsonPath('data.forms.business.name', 'متجر أحمد')
        ->assertJsonPath('data.forms.business.brand_mark.type', 'image')
        ->assertJsonPath('data.steps.0.done', true);

    expect($tenant->fresh()->meta->get('industry'))->toBe('retail')
        ->and($tenant->fresh()->name)->toBe('متجر أحمد')
        ->and(data_get($tenant->fresh()->meta, 'bio'))->toBe('نبيع منتجات مميزة');
});

it('autosaves business fields partially', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/dashboard/onboarding/business', [
            'partial' => true,
            'name' => 'اسم مؤقت',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.business.name', 'اسم مؤقت');

    expect($tenant->fresh()->name)->toBe('اسم مؤقت')
        ->and(data_get($tenant->fresh()->meta, 'industry'))->toBeNull();
});

it('saves business step with an emoji brand mark', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/dashboard/onboarding/business', [
            'industry' => 'retail',
            'name' => 'متجر بالإيموجي',
            'bio' => 'نبذة قصيرة عن النشاط',
            'brand_mark_type' => 'emoji',
            'brand_mark_value' => '🚀',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.business.brand_mark.type', 'emoji')
        ->assertJsonPath('data.forms.business.brand_mark.value', '🚀')
        ->assertJsonPath('data.steps.0.done', true);

    expect(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('emoji')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.value'))->toBe('🚀');
});

it('saves business step with an icon brand mark', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/dashboard/onboarding/business', [
            'industry' => 'retail',
            'name' => 'متجر بالأيقونة',
            'bio' => 'نبذة قصيرة عن النشاط',
            'brand_mark_type' => 'icon',
            'brand_mark_value' => 'tabler:chart-line',
            'brand_mark_color' => '#DC2626',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.business.brand_mark.type', 'icon')
        ->assertJsonPath('data.forms.business.brand_mark.value', 'tabler:chart-line')
        ->assertJsonPath('data.forms.business.brand_mark.color', '#dc2626')
        ->assertJsonPath('data.steps.0.done', true);

    expect(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('icon')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.value'))->toBe('tabler:chart-line');
});

it('saves contact step with optional social username', function () {
    [$user] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/contact', [
            'phone' => '0501234567',
            'email' => 'hello@example.com',
            'whatsapp_same_as_phone' => true,
            'country' => 'SA',
            'city' => 'الرياض',
            'social_links' => [
                ['network' => 'twitter', 'username' => 'eqleem'],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.contact.whatsapp', '0501234567')
        ->assertJsonPath('data.forms.contact.social_links.0.url', 'https://x.com/eqleem');
});

it('marks contact done with only phone and email', function () {
    [$user] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/contact', [
            'phone' => '0501234567',
            'email' => 'hello@example.com',
            'whatsapp_same_as_phone' => true,
            'social_links' => [],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.steps.1.done', true);
});

it('saves identity theme options including handle', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();
    $handle = 'brand-'.Str::lower(Str::random(5));

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/identity', [
            'handle' => $handle,
            'primary_color' => 'teal',
            'font_family' => 'effra',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.identity.primary_color', 'teal')
        ->assertJsonPath('data.forms.identity.handle', $handle)
        ->assertJsonPath('data.forms.identity.font_family', 'effra');

    $saved = $tenant->fresh()->themeSettingsFor((int) $tenant->theme_id);

    expect($tenant->fresh()->handle)->toBe($handle)
        ->and($saved['primaryColor'])->toBe('teal')
        ->and($saved['fontFamily'])->toBe('effra')
        ->and($saved['logoRadius'])->not->toBeEmpty();
});

it('saves goal action types', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/goal', [
            'primary_action_type' => 'whatsapp-chat',
            'secondary_action_type' => 'store-link',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.goal.primary_action_type', 'whatsapp-chat')
        ->assertJsonPath('data.forms.goal.secondary_action_type', 'store-link')
        ->assertJsonPath('data.steps.3.done', true);

    expect(data_get($tenant->fresh()->meta, 'primary_action_type'))->toBe('whatsapp-chat')
        ->and(data_get($tenant->fresh()->meta, 'secondary_action_type'))->toBe('store-link');
});

it('exposes onboarding fonts without milligram or eqleem and includes effra', function () {
    [$user] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->getJson('/api/dashboard/onboarding')
        ->assertSuccessful()
        ->assertJsonPath('data.fonts.sarmady', 'سرمدي')
        ->assertJsonPath('data.fonts.ibmps', 'IBM Plex')
        ->assertJsonPath('data.fonts.effra', 'Effra')
        ->assertJsonMissingPath('data.fonts.milligram')
        ->assertJsonMissingPath('data.fonts.eqleem');
});

it('saves catalog enabled content types', function () {
    [$user, $tenant] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/catalog', [
            'enabled' => ['store', 'digital-products'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.forms.catalog.enabled', ['store', 'digital-products']);

    expect(data_get($tenant->fresh()->config, 'enabled_content_types'))->toBe(['store', 'digital-products']);
});

it('rejects empty catalog selection', function () {
    [$user] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->putJson('/api/dashboard/onboarding/catalog', [
            'enabled' => [],
        ])
        ->assertUnprocessable();
});

it('rejects dismissing incomplete onboarding', function () {
    [$user] = createOnboardingUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/dashboard/onboarding/dismiss')
        ->assertUnprocessable();
});

it('requires authentication', function () {
    $this->getJson('/api/dashboard/onboarding')->assertUnauthorized();
});
