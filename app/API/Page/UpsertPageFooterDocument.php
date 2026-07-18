<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockBrandMark;
use App\Support\BlockTypeRegistry;
use App\Support\BusinessDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpsertPageFooterDocument
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(BusinessDocuments::definitions()))],
            'custom_label' => ['nullable', 'string', 'max:100', 'required_if:type,other'],
            'value' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'brand_mark_type' => ['nullable', 'string', Rule::in(['image', 'emoji', 'icon', 'none'])],
            'brand_mark_value' => ['nullable', 'string', 'max:64'],
            'brand_mark_color' => ['nullable', 'string', 'max:20'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $blockId, array $data, ?string $documentId = null): array
    {
        setCurrentTenant($tenant);

        $oldImagePath = null;

        DB::transaction(function () use ($tenant, $blockId, $data, $documentId, &$oldImagePath): void {
            $block = Block::queryForTenantRoots()
                ->type('footer')
                ->whereKey($blockId)
                ->lockForUpdate()
                ->first();

            if (! $block) {
                throw new NotFoundHttpException;
            }

            $blockData = is_array($block->data) ? $block->data : [];
            $documents = BusinessDocuments::documentsForStorage($blockData);
            $index = $documentId === null
                ? null
                : collect($documents)->search(fn (array $document): bool => $document['id'] === $documentId);

            if ($documentId !== null && $index === false) {
                throw new NotFoundHttpException;
            }

            if ($documentId === null && count($documents) >= 20) {
                throw ValidationException::withMessages([
                    'type' => 'يمكن إضافة 20 وثيقة كحد أقصى.',
                ]);
            }

            $existing = $index === null ? null : $documents[$index];
            $existingMark = is_array($existing['brand_mark'] ?? null) ? $existing['brand_mark'] : null;
            $markChanged = ($data['logo'] ?? null) instanceof UploadedFile
                || array_key_exists('brand_mark_type', $data)
                || (bool) ($data['remove_logo'] ?? false);
            $brandMark = BlockBrandMark::resolveStored($tenant, $blockId, $data, $existingMark);

            if ($brandMark === null && $existing === null && $data['type'] === 'other') {
                $brandMark = [
                    'type' => 'icon',
                    'value' => 'tabler:file-certificate',
                    'color' => '',
                ];
            }

            $document = [
                'id' => $existing['id'] ?? (string) Str::uuid(),
                'type' => (string) $data['type'],
                'custom_label' => trim((string) ($data['custom_label'] ?? '')),
                'value' => trim((string) $data['value']),
                'brand_mark' => $brandMark,
                'legacy_logo' => $markChanged
                    ? ''
                    : (string) ($existing['legacy_logo'] ?? BusinessDocuments::defaultLogo((string) $data['type'])),
            ];

            if ($existing !== null) {
                $oldImagePath = ($existingMark['type'] ?? '') === 'image'
                    ? ($existingMark['path'] ?? null)
                    : null;
                $documents[$index] = $document;
            } else {
                $documents[] = $document;
            }

            $block->update([
                'data' => [
                    ...$blockData,
                    'documents' => array_values($documents),
                ],
            ]);

            if (($brandMark['path'] ?? null) === $oldImagePath) {
                $oldImagePath = null;
            }
        });

        if (filled($oldImagePath)) {
            Storage::disk('spaces')->delete((string) $oldImagePath);
        }

        return ShowPageBlock::make()->handle($tenant, $blockId, app(BlockTypeRegistry::class))['editor'];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, ?string $documentId = null): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo');
        }

        if ($request->boolean('remove_logo')) {
            $validated['remove_logo'] = true;
        }

        return $this->handle($this->currentDashboardTenant($request), $id, $validated, $documentId);
    }

    /**
     * @param  array<string, mixed>  $editor
     */
    public function jsonResponse(array $editor): JsonResponse
    {
        return response()->json([
            'data' => $editor,
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
