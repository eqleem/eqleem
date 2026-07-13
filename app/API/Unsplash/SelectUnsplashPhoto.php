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
 * Marks an Unsplash photo as selected (triggers download tracking) and returns its URL.
 */
class SelectUnsplashPhoto
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:64'],
        ];
    }

    /**
     * @return array{data: array{id: string, url: string, author: string, author_url: string|null}}
     */
    public function handle(string $id): array
    {
        $unsplash = app(Unsplash::class);

        if (! $unsplash->configured()) {
            throw new HttpException(503, 'Unsplash API key is not configured.');
        }

        try {
            return [
                'data' => $unsplash->select($id),
            ];
        } catch (RuntimeException $exception) {
            throw new HttpException(502, $exception->getMessage(), $exception);
        }
    }

    /**
     * @return array{data: array{id: string, url: string, author: string, author_url: string|null}}
     */
    public function asController(ActionRequest $request): array
    {
        $this->currentDashboardTenant($request);

        /** @var array{id: string} $validated */
        $validated = $request->validated();

        return $this->handle($validated['id']);
    }

    /**
     * @param  array{data: array{id: string, url: string, author: string, author_url: string|null}}  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json($payload);
    }
}
