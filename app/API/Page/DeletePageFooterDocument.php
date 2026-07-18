<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use App\Support\BusinessDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeletePageFooterDocument
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $blockId, string $documentId): array
    {
        setCurrentTenant($tenant);

        $imagePath = null;

        DB::transaction(function () use ($blockId, $documentId, &$imagePath): void {
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
            $document = collect($documents)->firstWhere('id', $documentId);

            if (! $document) {
                throw new NotFoundHttpException;
            }

            $mark = is_array($document['brand_mark'] ?? null) ? $document['brand_mark'] : [];
            $imagePath = ($mark['type'] ?? '') === 'image' ? ($mark['path'] ?? null) : null;

            $block->update([
                'data' => [
                    ...$blockData,
                    'documents' => collect($documents)
                        ->reject(fn (array $item): bool => $item['id'] === $documentId)
                        ->values()
                        ->all(),
                ],
            ]);
        });

        if (filled($imagePath)) {
            Storage::disk('spaces')->delete((string) $imagePath);
        }

        return ShowPageBlock::make()->handle($tenant, $blockId, app(BlockTypeRegistry::class))['editor'];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, string $documentId): array
    {
        return $this->handle($this->currentDashboardTenant($request), $id, $documentId);
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
