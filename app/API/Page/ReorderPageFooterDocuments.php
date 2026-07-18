<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use App\Support\BusinessDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReorderPageFooterDocuments
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1', 'max:20'],
            'order.*' => ['required', 'string', 'distinct'],
        ];
    }

    /**
     * @param  list<string>  $order
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $blockId, array $order): array
    {
        setCurrentTenant($tenant);

        DB::transaction(function () use ($blockId, $order): void {
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
            $existingIds = collect($documents)->pluck('id')->sort()->values()->all();
            $requestedIds = collect($order)->sort()->values()->all();

            if ($existingIds !== $requestedIds) {
                throw ValidationException::withMessages([
                    'order' => 'يجب أن يتضمن الترتيب جميع الوثائق الحالية فقط.',
                ]);
            }

            $documentsById = collect($documents)->keyBy('id');
            $ordered = collect($order)
                ->map(fn (string $id): array => $documentsById->get($id))
                ->values()
                ->all();

            $block->update([
                'data' => [
                    ...$blockData,
                    'documents' => $ordered,
                ],
            ]);
        });

        return ShowPageBlock::make()->handle($tenant, $blockId, app(BlockTypeRegistry::class))['editor'];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id): array
    {
        /** @var array{order: list<string>} $validated */
        $validated = $request->validated();

        return $this->handle($this->currentDashboardTenant($request), $id, $validated['order']);
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
