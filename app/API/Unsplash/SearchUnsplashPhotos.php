<?php

namespace App\API\Unsplash;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Support\Unsplash;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Search Unsplash (or return popular photos when query is empty).
 */
class SearchUnsplashPhotos
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ];
    }

    /**
     * @return array{data: list<array<string, mixed>>}
     */
    public function handle(?string $query = null, int $perPage = 16): array
    {
        $unsplash = app(Unsplash::class);

        if (! $unsplash->configured()) {
            throw new HttpException(503, 'Unsplash API key is not configured.');
        }

        try {
            return [
                'data' => $unsplash->photos($query, $perPage),
            ];
        } catch (RuntimeException $exception) {
            throw new HttpException(502, $exception->getMessage(), $exception);
        }
    }

    /**
     * @return array{data: list<array<string, mixed>>}
     */
    public function asController(ActionRequest $request): array
    {
        $this->currentDashboardTenant($request);

        /** @var array{query?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        $query = isset($validated['query']) ? trim((string) $validated['query']) : null;

        return $this->handle(
            filled($query) ? $query : null,
            (int) ($validated['per_page'] ?? 16),
        );
    }

    /**
     * @param  array{data: list<array<string, mixed>>}  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
