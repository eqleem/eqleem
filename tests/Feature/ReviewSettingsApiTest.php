<?php

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createReviewSettingsTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'reviews-settings-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'إعدادات التقييمات',
        'handle' => 'review-settings-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant];
}

test('guests cannot read or update review settings', function () {
    $this->getJson('/api/reviews/settings')->assertUnauthorized();
    $this->putJson('/api/reviews/settings', [
        'section_title' => 'آراء العملاء',
        'per_page' => 10,
    ])->assertUnauthorized();
});

test('owner can get and update review settings', function () {
    [$user] = createReviewSettingsTenant();

    $this->actingAs($user)
        ->getJson('/api/reviews/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'التقييمات')
        ->assertJsonPath('data.per_page', 12);

    $this->actingAs($user)
        ->putJson('/api/reviews/settings', [
            'section_title' => 'آراء عملائنا',
            'per_page' => 8,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'آراء عملائنا')
        ->assertJsonPath('data.per_page', 8);

    expect(Setting::reviewSettings())->toMatchArray([
        'section_title' => 'آراء عملائنا',
        'per_page' => 8,
    ]);
});

test('review settings validation rejects invalid per page values', function () {
    [$user] = createReviewSettingsTenant();

    $this->actingAs($user)
        ->putJson('/api/reviews/settings', [
            'section_title' => 'أ',
            'per_page' => 100,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['section_title', 'per_page']);
});
