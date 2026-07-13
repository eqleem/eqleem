<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns active content types from config/content-types.php for dashboard nav/tabs.
 */
class ListContentTypes
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array{data: list<array<string, mixed>>}
     */
    public function handle(ContentTypeRegistry $contentTypes): array
    {
        return [
            'data' => $contentTypes->tabs(),
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>}
     */
    public function asController(ActionRequest $request, ContentTypeRegistry $contentTypes): array
    {
        $this->currentDashboardTenant($request);

        return $this->handle($contentTypes);
    }

    /**
     * @param  array{data: list<array<string, mixed>>}  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
