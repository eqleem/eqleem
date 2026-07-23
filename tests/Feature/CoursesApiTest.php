<?php

use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForCourses(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'دورات',
        'handle' => 'courses-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access courses endpoints', function () {
    $this->getJson('/api/courses')->assertUnauthorized();
    $this->postJson('/api/courses', ['title' => 'دورة'])->assertUnauthorized();
    $this->getJson('/api/courses/categories')->assertUnauthorized();
    $this->getJson('/api/courses/settings')->assertUnauthorized();
});

test('owner can create list update and delete courses', function () {
    [$user, $tenant] = createUserWithTenantForCourses();

    $create = $this->actingAs($user)
        ->postJson('/api/courses', ['title' => 'دورة Laravel'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'دورة Laravel')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/courses')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/courses/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'دورة Laravel')
        ->assertJsonStructure([
            'data' => [
                'category_options',
                'images',
                'chapters',
                'slug_prefix',
                'price',
                'compare_price',
                'currency_code',
                'currency_symbol',
                'subtitle',
                'level_options',
                'course_type_options',
            ],
        ])
        ->assertJsonPath('data.currency_code', 'SAR')
        ->assertJsonPath('data.currency_symbol', Money::SAR_SYMBOL);

    setCurrentTenant($tenant);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'برمجة',
        'type' => 'course_category',
        'sort_order' => 0,
    ]);

    $chapterId = (string) Str::uuid();
    $lessonId = (string) Str::uuid();

    $this->actingAs($user)
        ->putJson("/api/courses/{$uuid}", [
            'title' => 'دورة Laravel محدثة',
            'subtitle' => 'من الصفر للاحتراف',
            'body' => '<p>محتوى الدورة</p>',
            'slug' => 'laravel-course',
            'price' => 199.99,
            'compare_price' => 299,
            'hours' => 12,
            'level' => 'beginner',
            'course_type' => 'recorded',
            'category_ids' => [$leaf->id],
            'published' => true,
            'chapters' => [
                [
                    'id' => $chapterId,
                    'title' => 'الأساسيات',
                    'description' => 'مقدمة',
                    'lessons' => [
                        [
                            'id' => $lessonId,
                            'title' => 'مقدمة',
                            'description' => 'درس أول',
                            'source' => 'link',
                            'link' => 'https://example.com/lesson-1',
                        ],
                    ],
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'دورة Laravel محدثة')
        ->assertJsonPath('data.subtitle', 'من الصفر للاحتراف')
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.price', '199.99')
        ->assertJsonPath('data.compare_price', '299')
        ->assertJsonPath('data.hours', '12')
        ->assertJsonPath('data.level', 'beginner')
        ->assertJsonPath('data.course_type', 'recorded')
        ->assertJsonPath('data.category_ids.0', (string) $leaf->id)
        ->assertJsonPath('data.chapters.0.title', 'الأساسيات')
        ->assertJsonPath('data.chapters.0.lessons.0.title', 'مقدمة');

    setCurrentTenant($tenant);

    $course = Content::query()->where('uuid', $uuid)->first();

    expect($course)->not->toBeNull()
        ->and($course->status)->toBe('published')
        ->and(data_get($course->data, 'price'))->toBe(money_minor(199.99))
        ->and(data_get($course->data, 'chapters.0.lessons.0.link'))->toBe('https://example.com/lesson-1');

    $this->actingAs($user)
        ->deleteJson('/api/courses', ['ids' => [$course->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);

    setCurrentTenant($tenant);

    expect(Content::query()->where('uuid', $uuid)->exists())->toBeFalse();
});

test('owner can manage course categories', function () {
    [$user, $tenant] = createUserWithTenantForCourses();

    $first = $this->actingAs($user)
        ->postJson('/api/courses/categories', ['name' => 'تصميم'])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'تصميم');

    $firstId = (int) $first->json('data.category.id');

    $this->actingAs($user)
        ->deleteJson("/api/courses/categories/{$firstId}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($firstId)->exists())->toBeFalse();
});

test('owner can get and update course settings', function () {
    [$user] = createUserWithTenantForCourses();

    $this->actingAs($user)
        ->getJson('/api/courses/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'الدورات التدريبية');

    $this->actingAs($user)
        ->putJson('/api/courses/settings', [
            'section_title' => 'أكاديميتنا',
            'section_description' => 'أفضل الدورات التعليمية',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'أكاديميتنا');

    expect(Setting::courseSettings()['section_title'])->toBe('أكاديميتنا');
});

test('owner can upload cover image and lesson files', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForCourses();

    $create = $this->actingAs($user)
        ->postJson('/api/courses', ['title' => 'دورة ملفات'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');
    $chapterId = (string) Str::uuid();
    $lessonId = (string) Str::uuid();

    $this->actingAs($user)
        ->post("/api/courses/{$uuid}/cover-image", [
            'file' => UploadedFile::fake()->image('course.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');

    $this->actingAs($user)
        ->post("/api/courses/{$uuid}/lesson-files", [
            'file' => UploadedFile::fake()->create('lesson.mp4', 100, 'video/mp4'),
            'chapter_id' => $chapterId,
            'lesson_id' => $lessonId,
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.chapter_id', $chapterId)
        ->assertJsonPath('data.lesson_id', $lessonId)
        ->assertJsonStructure(['data' => ['media_id', 'file_name', 'file_url']]);
});
