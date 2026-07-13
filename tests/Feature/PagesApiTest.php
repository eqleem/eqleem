<?php

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForPages(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحات تجريبية',
        'handle' => 'pages-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access pages endpoints', function () {
    $this->getJson('/api/pages')->assertUnauthorized();
    $this->postJson('/api/pages', ['title' => 'صفحة'])->assertUnauthorized();
});

test('owner can create list update and delete pages', function () {
    [$user, $tenant] = createUserWithTenantForPages();

    $create = $this->actingAs($user)
        ->postJson('/api/pages', ['title' => 'من نحن'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'من نحن')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/pages')
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonFragment(['uuid' => $uuid]);

    $this->actingAs($user)
        ->getJson("/api/pages/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'من نحن')
        ->assertJsonStructure([
            'data' => [
                'slug_prefix',
            ],
        ]);

    $this->actingAs($user)
        ->putJson("/api/pages/{$uuid}", [
            'title' => 'من نحن - محدث',
            'subtitle' => 'تعرف علينا',
            'body' => '<p>محتوى الصفحة</p>',
            'slug' => 'about-us',
            'published' => true,
            'editor_mode' => 'html',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'من نحن - محدث')
        ->assertJsonPath('data.subtitle', 'تعرف علينا')
        ->assertJsonPath('data.published', true);

    setCurrentTenant($tenant);

    $page = Content::query()->where('uuid', $uuid)->first();

    expect($page)->not->toBeNull()
        ->and($page->status)->toBe('published')
        ->and($page->published_at)->not->toBeNull()
        ->and(data_get($page->data, 'subtitle'))->toBe('تعرف علينا')
        ->and(data_get($page->data, 'body'))->toBe('<p>محتوى الصفحة</p>');

    $this->actingAs($user)
        ->putJson("/api/pages/{$uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    $this->actingAs($user)
        ->deleteJson('/api/pages', ['ids' => [$page->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);
});

test('system pages cannot be deleted via api', function () {
    [$user, $tenant] = createUserWithTenantForPages();

    setCurrentTenant($tenant);

    $systemPage = Content::query()
        ->type(contentTypeModel('pages'))
        ->where('template', 'faq')
        ->firstOrFail();

    expect($systemPage->isSystemPage())->toBeTrue();

    $this->actingAs($user)
        ->deleteJson('/api/pages', ['ids' => [$systemPage->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 0);

    expect(Content::query()->whereKey($systemPage->id)->exists())->toBeTrue();
});

test('owner can manage page blocks on a content page', function () {
    [$user, $tenant] = createUserWithTenantForPages();

    $create = $this->actingAs($user)
        ->postJson('/api/pages', ['title' => 'صفحة ببلوكات'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson("/api/pages/{$uuid}/blocks")
        ->assertSuccessful()
        ->assertJsonPath('data', [])
        ->assertJsonStructure(['block_types']);

    $blockCreate = $this->actingAs($user)
        ->postJson("/api/pages/{$uuid}/blocks", ['type' => 'block-link'])
        ->assertSuccessful()
        ->assertJsonPath('data.type', 'block-link')
        ->assertJsonPath('data.active', true);

    $blockId = (int) $blockCreate->json('data.id');

    $this->actingAs($user)
        ->getJson("/api/pages/{$uuid}/blocks")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->actingAs($user)
        ->putJson("/api/pages/{$uuid}/blocks/{$blockId}", [
            'link_type' => 'section:blog',
            'title' => 'زر مخصص',
            'description' => 'وصف الرابط',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.block.title', 'زر مخصص')
        ->assertJsonPath('data.editor.type', 'block-link');

    $this->actingAs($user)
        ->putJson("/api/pages/{$uuid}/blocks/{$blockId}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    setCurrentTenant($tenant);

    expect(Block::queryForContent(Content::query()->where('uuid', $uuid)->value('id'))
        ->userBlocks()
        ->find($blockId)
        ?->active)->toBeFalse();

    $this->actingAs($user)
        ->deleteJson("/api/pages/{$uuid}/blocks/{$blockId}")
        ->assertSuccessful();

    $this->actingAs($user)
        ->getJson("/api/pages/{$uuid}/blocks")
        ->assertSuccessful()
        ->assertJsonPath('data', []);
});

test('owner can reorder page blocks', function () {
    [$user] = createUserWithTenantForPages();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/pages', ['title' => 'ترتيب البلوكات'])
        ->json('data.uuid');

    $firstId = (int) $this->actingAs($user)
        ->postJson("/api/pages/{$uuid}/blocks", ['type' => 'block-link'])
        ->json('data.id');

    $secondId = (int) $this->actingAs($user)
        ->postJson("/api/pages/{$uuid}/blocks", ['type' => 'block-link'])
        ->json('data.id');

    $this->actingAs($user)
        ->putJson("/api/pages/{$uuid}/blocks/reorder", ['order' => [$secondId, $firstId]])
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $secondId)
        ->assertJsonPath('data.1.id', $firstId);
});

test('owner can update contact and faq template pages', function () {
    [$user, $tenant] = createUserWithTenantForPages();

    setCurrentTenant($tenant);

    $contact = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('contact')
        ->firstOrFail();

    $this->actingAs($user)
        ->getJson("/api/pages/{$contact->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.template', 'contact')
        ->assertJsonPath('data.show_form', true)
        ->assertJsonPath('data.form_fields.name', true)
        ->assertJsonPath('data.show_social_links', true);

    $this->actingAs($user)
        ->putJson("/api/pages/{$contact->uuid}", [
            'title' => 'تواصل معنا',
            'subtitle' => 'وصف التواصل',
            'slug' => 'get-in-touch',
            'published' => true,
            'show_form' => true,
            'form_fields' => [
                'name' => true,
                'email' => false,
                'phone' => true,
                'message' => true,
                'address' => true,
                'subject' => false,
            ],
            'show_social_links' => false,
            'show_contact_info' => true,
            'show_extra_links' => false,
            'success_message' => 'تم الاستلام بنجاح',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.subtitle', 'وصف التواصل')
        ->assertJsonPath('data.form_fields.email', false)
        ->assertJsonPath('data.form_fields.address', true)
        ->assertJsonPath('data.show_social_links', false)
        ->assertJsonPath('data.show_extra_links', false)
        ->assertJsonPath('data.success_message', 'تم الاستلام بنجاح');

    $faq = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('faq')
        ->firstOrFail();

    $this->actingAs($user)
        ->getJson("/api/pages/{$faq->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.template', 'faq')
        ->assertJsonPath('data.faqs', []);

    $this->actingAs($user)
        ->putJson("/api/pages/{$faq->uuid}", [
            'title' => 'أسئلة شائعة',
            'subtitle' => 'اقرأ الإجابات',
            'slug' => 'common-questions',
            'published' => true,
            'faqs' => [
                [
                    'id' => 'q1',
                    'question' => 'كيف أبدأ؟',
                    'answer' => 'من لوحة التحكم.',
                ],
                [
                    'question' => 'هل يوجد دعم؟',
                    'answer' => 'نعم.',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.faqs.0.question', 'كيف أبدأ؟')
        ->assertJsonPath('data.faqs.0.answer', 'من لوحة التحكم.')
        ->assertJsonPath('data.faqs.1.question', 'هل يوجد دعم؟')
        ->assertJsonCount(2, 'data.faqs');
});

test('create page rejects unknown templates', function () {
    [$user] = createUserWithTenantForPages();

    $this->actingAs($user)
        ->postJson('/api/pages', [
            'title' => 'صفحة',
            'template' => 'pricing',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['template']);
});

test('create page rejects duplicate contact and faq templates', function () {
    [$user] = createUserWithTenantForPages();

    $this->actingAs($user)
        ->postJson('/api/pages', [
            'title' => 'اتصل بنا مرة أخرى',
            'template' => 'contact',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['template']);

    $this->actingAs($user)
        ->postJson('/api/pages', [
            'title' => 'أسئلة مرة أخرى',
            'template' => 'faq',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['template']);
});

test('pages list exposes existing templates', function () {
    [$user] = createUserWithTenantForPages();

    $this->actingAs($user)
        ->getJson('/api/pages')
        ->assertSuccessful()
        ->assertJsonPath('existing_templates', fn ($templates) => collect($templates)->sort()->values()->all() === ['contact', 'faq']);
});

test('pages search filters by title', function () {
    [$user, $tenant] = createUserWithTenantForPages();

    setCurrentTenant($tenant);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('pages'),
        'title' => 'About Company',
        'slug' => 'about-company',
        'status' => 'draft',
        'active' => true,
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('pages'),
        'title' => 'Pricing Page',
        'slug' => 'pricing-page',
        'status' => 'draft',
        'active' => true,
    ]);

    $this->actingAs($user)
        ->getJson('/api/pages?'.http_build_query(['search' => 'About']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'About Company');
});
