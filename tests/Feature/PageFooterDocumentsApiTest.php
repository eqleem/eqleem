<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Support\BusinessDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Block}
 */
function createFooterDocumentsTenant(): array
{
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الوثائق',
        'handle' => 'footer-documents-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh(), Block::findSingleton('footer')];
}

test('guests cannot manage footer documents', function () {
    $this->postJson('/api/page/blocks/1/footer-documents', [
        'type' => 'vat',
        'value' => '123',
    ])->assertUnauthorized();
});

test('owner can create update reorder and delete footer documents', function () {
    [$user, $tenant, $footer] = createFooterDocumentsTenant();

    $first = $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$footer->id.'/footer-documents', [
            'type' => 'other',
            'custom_label' => 'ضمان الجودة',
            'value' => 'QA-100',
            'brand_mark_type' => 'icon',
            'brand_mark_value' => 'tabler:shield-check',
            'brand_mark_color' => '#16A34A',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.documents.0.label', 'ضمان الجودة')
        ->assertJsonPath('data.documents.0.brand_mark.type', 'icon')
        ->assertJsonPath('data.documents.0.brand_mark.color', '#16a34a');

    $firstId = (string) $first->json('data.documents.0.id');

    $second = $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$footer->id.'/footer-documents', [
            'type' => 'commercial_register',
            'value' => 'CR-200',
        ])
        ->assertSuccessful();

    $secondId = (string) $second->json('data.documents.1.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$footer->id.'/footer-documents/reorder', [
            'order' => [$secondId, $firstId],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.documents.0.id', $secondId)
        ->assertJsonPath('data.documents.1.id', $firstId);

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$footer->id.'/footer-documents/'.$firstId, [
            'type' => 'other',
            'custom_label' => 'ضمان محدث',
            'value' => 'QA-101',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.documents.1.label', 'ضمان محدث')
        ->assertJsonPath('data.documents.1.value', 'QA-101');

    $this->actingAs($user)
        ->deleteJson('/api/page/blocks/'.$footer->id.'/footer-documents/'.$secondId)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.documents')
        ->assertJsonPath('data.documents.0.id', $firstId);

    setCurrentTenant($tenant);
    expect(data_get($footer->fresh()->data, 'documents.0.id'))->toBe($firstId);
});

test('other footer documents require a custom label', function () {
    [$user, , $footer] = createFooterDocumentsTenant();

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$footer->id.'/footer-documents', [
            'type' => 'other',
            'value' => '123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['custom_label']);
});

test('owner can upload an image for a footer document', function () {
    Storage::fake('spaces');

    [$user, , $footer] = createFooterDocumentsTenant();

    $this->actingAs($user)
        ->post('/api/page/blocks/'.$footer->id.'/footer-documents', [
            'type' => 'vat',
            'value' => '310123456700003',
            'brand_mark_type' => 'image',
            'logo' => UploadedFile::fake()->image('vat.png', 200, 200),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.documents.0.brand_mark.type', 'image');

    $path = data_get($footer->fresh()->data, 'documents.0.brand_mark.path');

    expect($path)->not->toBeEmpty();
    Storage::disk('spaces')->assertExists($path);
});

test('legacy document numbers remain visible and become reorderable documents', function () {
    [$user, , $footer] = createFooterDocumentsTenant();

    $footer->update([
        'data' => [
            'show_documents_warranties' => true,
            'document_numbers' => [
                'vat' => '310000000000003',
                'commercial_register' => '1010000000',
            ],
        ],
    ]);

    $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertSuccessful()
        ->assertJsonPath('data.bottom_blocks.0.editor.documents.0.id', 'legacy-vat')
        ->assertJsonPath('data.bottom_blocks.0.editor.documents.1.id', 'legacy-commercial_register');

    $visible = BusinessDocuments::visibleForBlockData($footer->fresh()->data);

    expect($visible)->toHaveCount(2)
        ->and($visible->first()['label'])->toBe('الرقم الضريبي')
        ->and($visible->first()['brand_mark']['type'])->toBe('image');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$footer->id.'/footer-documents/reorder', [
            'order' => ['legacy-commercial_register', 'legacy-vat'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.documents.0.id', 'legacy-commercial_register');
});

test('footer view renders ordered documents with their selected marks', function () {
    $documents = BusinessDocuments::visibleForBlockData([
        'documents' => [
            [
                'id' => 'quality',
                'type' => 'other',
                'custom_label' => 'ضمان الجودة',
                'value' => 'QA-100',
                'brand_mark' => [
                    'type' => 'icon',
                    'value' => 'tabler:shield-check',
                    'color' => '#16a34a',
                ],
            ],
        ],
    ]);

    $html = view()->file(public_path('themes/default/blocks/footer.blade.php'), [
        'showDocumentsWarranties' => true,
        'businessDocuments' => $documents,
        'footerLinks' => collect(),
    ])->render();

    expect($html)
        ->toContain('ضمان الجودة')
        ->toContain('QA-100')
        ->toContain('tabler:shield-check');
});

test('footer document reorder rejects partial lists', function () {
    [$user, , $footer] = createFooterDocumentsTenant();

    foreach (['vat', 'freelance'] as $type) {
        $this->actingAs($user)->postJson('/api/page/blocks/'.$footer->id.'/footer-documents', [
            'type' => $type,
            'value' => Str::upper($type),
        ])->assertSuccessful();
    }

    $firstId = data_get($footer->fresh()->data, 'documents.0.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$footer->id.'/footer-documents/reorder', [
            'order' => [$firstId],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order']);
});
