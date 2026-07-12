<?php

use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use App\Support\TenantThemeOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Theme, 3: Theme}
 */
function createUserWithThemesForPageDesign(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $default = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إفتراضي',
        'slug' => 'default',
        'meta' => [
            'label_ar' => 'إفتراضي',
            'preview' => 'assets/wjeez/themes/default.svg',
            'gallery' => [
                'https://api.dicebear.com/10.x/stripes/svg?seed=default-home',
                'https://api.dicebear.com/10.x/stripes/svg?seed=default-store',
                'https://api.dicebear.com/10.x/stripes/svg?seed=default-blog',
                'https://api.dicebear.com/10.x/stripes/svg?seed=default-services',
                'https://api.dicebear.com/10.x/stripes/svg?seed=default-contact',
            ],
            'designer' => 'فريق إقليم',
            'price' => 0,
            'version' => '2.1.0',
            'description' => 'قالب متكامل بتصميم عصري.',
            'features' => [
                'تصميم متجاوب',
                'ألوان قابلة للتخصيص',
            ],
        ],
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 1,
    ]);

    $minimal = Theme::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'بسيط',
        'slug' => 'minimal',
        'meta' => [
            'label_ar' => 'بسيط',
            'preview' => 'assets/wjeez/themes/minimal.svg',
            'gallery' => [
                'https://api.dicebear.com/10.x/stripes/svg?seed=minimal-home',
                'https://api.dicebear.com/10.x/stripes/svg?seed=minimal-store',
                'https://api.dicebear.com/10.x/stripes/svg?seed=minimal-blog',
                'https://api.dicebear.com/10.x/stripes/svg?seed=minimal-services',
                'https://api.dicebear.com/10.x/stripes/svg?seed=minimal-contact',
            ],
            'designer' => 'استوديو بسيط',
            'price' => 99,
            'version' => '1.4.2',
            'description' => 'قالب بسيط بأناقة عالية.',
            'features' => [
                'مظهر نظيف',
                'مساحات بيضاء مدروسة',
            ],
        ],
        'type' => 'all',
        'app' => 'all',
        'active' => true,
        'public' => true,
        'sort' => 2,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'page-design-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => $default->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh(), $default, $minimal];
}

test('guests cannot access page design', function () {
    $this->getJson('/api/page/design')->assertUnauthorized();
    $this->putJson('/api/page/design/theme', ['theme_id' => 1])->assertUnauthorized();
    $this->postJson('/api/page/design/options', ['theme_id' => 1])->assertUnauthorized();
});

test('owner can list themes and default theme options schema', function () {
    [$user, $tenant, $default] = createUserWithThemesForPageDesign();

    $this->actingAs($user)
        ->getJson('/api/page/design')
        ->assertSuccessful()
        ->assertJsonPath('data.selected_theme_id', $default->id)
        ->assertJsonPath('data.tenant_theme_id', $default->id)
        ->assertJsonPath('data.selected_theme.is_active', true)
        ->assertJsonPath('data.selected_theme.is_free', true)
        ->assertJsonPath('data.selected_theme.price_label', 'مجاني')
        ->assertJsonPath('data.selected_theme.version', '2.1.0')
        ->assertJsonPath('data.selected_theme.designer', 'فريق إقليم')
        ->assertJsonPath('data.selected_theme.description', 'قالب متكامل بتصميم عصري.')
        ->assertJsonPath('data.selected_theme.features.0', 'تصميم متجاوب')
        ->assertJsonPath('data.options_schema.primaryColor.type', 'picker-color')
        ->assertJsonPath('data.options_schema.primaryColor.allowCustom', true)
        ->assertJsonPath('data.options.primaryColor', 'blue')
        ->assertJsonPath('data.options.logoRadius', 'full')
        ->assertJsonFragment(['slug' => 'default'])
        ->assertJsonFragment(['slug' => 'minimal']);
});

test('theme info payload includes gallery and paid theme pricing', function () {
    [$user, $tenant, $default, $minimal] = createUserWithThemesForPageDesign();

    $response = $this->actingAs($user)
        ->getJson('/api/page/design?theme_id='.$minimal->id)
        ->assertSuccessful()
        ->assertJsonPath('data.selected_theme.is_free', false)
        ->assertJsonPath('data.selected_theme.version', '1.4.2')
        ->assertJsonPath('data.selected_theme.designer', 'استوديو بسيط');

    $gallery = $response->json('data.selected_theme.gallery');

    expect($gallery)->toBeArray()
        ->and(count($gallery))->toBeGreaterThanOrEqual(5)
        ->and($response->json('data.selected_theme.features'))->toHaveCount(2);
});

test('owner can inspect another theme without activating it', function () {
    [$user, $tenant, $default, $minimal] = createUserWithThemesForPageDesign();

    $this->actingAs($user)
        ->getJson('/api/page/design?theme_id='.$minimal->id)
        ->assertSuccessful()
        ->assertJsonPath('data.selected_theme_id', $minimal->id)
        ->assertJsonPath('data.tenant_theme_id', $default->id)
        ->assertJsonPath('data.selected_theme.is_active', false)
        ->assertJsonPath('data.options_schema', []);
});

test('owner can set default theme', function () {
    [$user, $tenant, $default, $minimal] = createUserWithThemesForPageDesign();

    $this->actingAs($user)
        ->putJson('/api/page/design/theme', ['theme_id' => $minimal->id])
        ->assertSuccessful()
        ->assertJsonPath('data.tenant_theme_id', $minimal->id)
        ->assertJsonPath('data.selected_theme_id', $minimal->id)
        ->assertJsonPath('data.selected_theme.is_active', true)
        ->assertJsonPath('message', 'تم تعيين القالب الافتراضي بنجاح.');

    expect($tenant->fresh()->theme_id)->toBe($minimal->id);
});

test('owner can save a custom hex primary color', function () {
    [$user, $tenant, $default] = createUserWithThemesForPageDesign();

    $this->actingAs($user)
        ->postJson('/api/page/design/options', [
            'theme_id' => $default->id,
            'options' => [
                'primaryColor' => '#a855f7',
                'bgColor' => 'gray-300',
                'logoRadius' => 'rounded-full',
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.options.primaryColor', '#a855f7');

    expect($tenant->fresh()->themeSettingsFor($default->id)['primaryColor'])->toBe('#a855f7');

    $palette = app(TenantThemeOptions::class)
        ->primaryPalette(['primaryColor' => '#a855f7']);

    expect($palette['500'])->toBe('#a855f7')
        ->and($palette)->toHaveKey('700');
});

test('owner can save theme options including image upload', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user, $tenant, $default] = createUserWithThemesForPageDesign();

    $file = UploadedFile::fake()->image('header.jpg', 800, 400);

    $this->actingAs($user)
        ->post('/api/page/design/options', [
            'theme_id' => $default->id,
            'options' => [
                'primaryColor' => 'teal',
                'bgColor' => 'stone-200',
                'logoRadius' => 'rounded-lg',
            ],
            'uploads' => [
                'headerImage' => $file,
            ],
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.options.primaryColor', 'teal')
        ->assertJsonPath('data.options.bgColor', 'stone-200')
        ->assertJsonPath('data.options.logoRadius', 'rounded-lg');

    $saved = $tenant->fresh()->themeSettingsFor($default->id);

    expect($saved['primaryColor'])->toBe('teal')
        ->and($saved['logoRadius'])->toBe('rounded-lg')
        ->and($saved['headerImage'])->not->toBeEmpty();
});

test('saving theme options without a new upload preserves existing header image', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user, $tenant, $default] = createUserWithThemesForPageDesign();

    $file = UploadedFile::fake()->image('header.jpg', 800, 400);

    $this->actingAs($user)
        ->post('/api/page/design/options', [
            'theme_id' => $default->id,
            'options' => [
                'primaryColor' => 'blue',
            ],
            'uploads' => [
                'headerImage' => $file,
            ],
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $existing = $tenant->fresh()->themeSettingsFor($default->id)['headerImage'];

    expect($existing)->not->toBeEmpty();

    $this->actingAs($user)
        ->post('/api/page/design/options', [
            'theme_id' => $default->id,
            'options' => [
                'primaryColor' => 'teal',
                'headerImage' => '',
            ],
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.options.primaryColor', 'teal')
        ->assertJsonPath('data.options.headerImage', $existing);

    expect($tenant->fresh()->themeSettingsFor($default->id)['headerImage'])->toBe($existing);
});
