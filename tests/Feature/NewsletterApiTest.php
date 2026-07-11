<?php

use App\Models\Content;
use App\Models\Setting;
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
function createUserWithTenantForNewsletter(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'نشرة تجريبية',
        'handle' => 'newsletter-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access newsletter endpoints', function () {
    $this->getJson('/api/newsletter')->assertUnauthorized();
    $this->postJson('/api/newsletter', ['title' => 'نشرة'])->assertUnauthorized();
    $this->getJson('/api/newsletter/settings')->assertUnauthorized();
});

test('owner can create list update and delete newsletter issues', function () {
    [$user, $tenant] = createUserWithTenantForNewsletter();

    $create = $this->actingAs($user)
        ->postJson('/api/newsletter', ['title' => 'نشرة أسبوعية'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نشرة أسبوعية')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false)
        ->assertJsonPath('data.mail_status', 'draft');

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/newsletter')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/newsletter/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نشرة أسبوعية')
        ->assertJsonStructure([
            'data' => [
                'mail_status_options',
                'slug_prefix',
            ],
        ]);

    $this->actingAs($user)
        ->putJson("/api/newsletter/{$uuid}", [
            'title' => 'نشرة أسبوعية محدثة',
            'subject' => 'موضوع البريد',
            'subtitle' => 'نص معاينة',
            'body' => '<p>محتوى النشرة</p>',
            'slug' => 'weekly-newsletter',
            'mail_status' => 'scheduled',
            'scheduled_date' => '2026-08-01',
            'scheduled_time' => '10:30',
            'recipients_count' => 120,
            'published' => true,
            'editor_mode' => 'html',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نشرة أسبوعية محدثة')
        ->assertJsonPath('data.subject', 'موضوع البريد')
        ->assertJsonPath('data.mail_status', 'scheduled')
        ->assertJsonPath('data.scheduled_date', '2026-08-01')
        ->assertJsonPath('data.scheduled_time', '10:30')
        ->assertJsonPath('data.recipients_count', 120)
        ->assertJsonPath('data.published', true);

    setCurrentTenant($tenant);

    $issue = Content::query()->where('uuid', $uuid)->first();

    expect($issue)->not->toBeNull()
        ->and($issue->status)->toBe('published')
        ->and($issue->published_at)->not->toBeNull()
        ->and(data_get($issue->data, 'subject'))->toBe('موضوع البريد')
        ->and(data_get($issue->data, 'mail_status'))->toBe('scheduled')
        ->and(data_get($issue->data, 'recipients_count'))->toBe(120);

    $this->actingAs($user)
        ->deleteJson('/api/newsletter', ['ids' => [$issue->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);
});

test('owner can read and update newsletter settings', function () {
    [$user] = createUserWithTenantForNewsletter();

    $this->actingAs($user)
        ->getJson('/api/newsletter/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'النشرة البريدية');

    $this->actingAs($user)
        ->putJson('/api/newsletter/settings', [
            'section_title' => 'نشرتنا',
            'section_description' => 'آخر أخبار المشروع والتحديثات',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'نشرتنا')
        ->assertJsonPath('data.section_description', 'آخر أخبار المشروع والتحديثات');

    expect(Setting::newsletterSettings()['section_title'])->toBe('نشرتنا');
});

test('owner can upload and delete newsletter featured image', function () {
    Storage::fake('spaces');

    [$user] = createUserWithTenantForNewsletter();

    $create = $this->actingAs($user)
        ->postJson('/api/newsletter', ['title' => 'نشرة بصورة'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/newsletter/{$uuid}/featured-image", [
            'file' => UploadedFile::fake()->image('cover.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.featured_image', fn (mixed $value): bool => filled($value));

    setCurrentTenant(Tenant::query()->first());

    $issue = Content::query()->where('uuid', $uuid)->first();

    expect(data_get($issue?->data, 'image'))->not->toBeNull();

    $this->actingAs($user)
        ->deleteJson("/api/newsletter/{$uuid}/featured-image")
        ->assertSuccessful()
        ->assertJsonPath('data.featured_image', null);

    expect(data_get($issue?->fresh()->data, 'image'))->toBeNull();
});

test('newsletter search filters by title subject and subtitle', function () {
    [$user, $tenant] = createUserWithTenantForNewsletter();

    setCurrentTenant($tenant);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('newsletter'),
        'title' => 'Tech Newsletter',
        'slug' => 'tech-newsletter',
        'status' => 'draft',
        'active' => true,
        'data' => [
            'subject' => 'Weekly updates',
            'mail_status' => 'draft',
        ],
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('newsletter'),
        'title' => 'Marketing Newsletter',
        'slug' => 'marketing-newsletter',
        'status' => 'draft',
        'active' => true,
        'data' => [
            'subtitle' => 'Special offers',
            'mail_status' => 'draft',
        ],
    ]);

    $this->actingAs($user)
        ->getJson('/api/newsletter?'.http_build_query(['search' => 'Tech']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Tech Newsletter');

    $this->actingAs($user)
        ->getJson('/api/newsletter?'.http_build_query(['search' => 'Weekly']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Tech Newsletter');

    $this->actingAs($user)
        ->getJson('/api/newsletter?'.http_build_query(['search' => 'offers']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Marketing Newsletter');
});
