<?php

namespace App\API\Icons;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Support\TablerIconsCatalog;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Searches the Tabler icon catalog with pagination for the brand-mark picker.
 */
class SearchTablerIcons
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }

    /**
     * @param  array{q?: string|null, page?: int|null, per_page?: int|null}  $data
     * @return array{
     *     data: list<array{id: string, name: string}>,
     *     meta: array{page: int, per_page: int, total: int, has_more: bool}
     * }
     */
    public function handle(array $data, TablerIconsCatalog $catalog): array
    {
        return $catalog->search(
            $data['q'] ?? null,
            (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 96),
        );
    }

    /**
     * @return array{
     *     data: list<array{id: string, name: string}>,
     *     meta: array{page: int, per_page: int, total: int, has_more: bool}
     * }
     */
    public function asController(ActionRequest $request, TablerIconsCatalog $catalog): array
    {
        $this->currentDashboardTenant($request);

        /** @var array{q?: string|null, page?: int|null, per_page?: int|null} $validated */
        $validated = $request->validated();

        return $this->handle($validated, $catalog);
    }

    /**
     * @param  array{
     *     data: list<array{id: string, name: string}>,
     *     meta: array{page: int, per_page: int, total: int, has_more: bool}
     * }  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
