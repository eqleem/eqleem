<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates a social link from the header block editor.
 */
class UpdatePageHeaderSocialLink
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'network' => ['required', 'string', Rule::in(array_keys(config('social-networks', [])))],
            'url' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array{network: string, url: string}  $data
     * @return list<array<string, mixed>>
     */
    public function handle(Tenant $tenant, string $id, array $data): array
    {
        setCurrentTenant($tenant);

        $updated = app(TenantProfileService::class)->updateSocialLink(
            $tenant,
            $id,
            $data['network'],
            $data['url'],
        );

        if ($updated === false) {
            throw new NotFoundHttpException('رابط التواصل غير موجود.');
        }

        return app(TenantProfileService::class)->socialLinks($tenant->fresh())->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function asController(ActionRequest $request, string $id): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{network: string, url: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, $validated);
    }

    /**
     * @param  list<array<string, mixed>>  $links
     */
    public function jsonResponse(array $links): JsonResponse
    {
        return response()->json([
            'data' => $links,
            'message' => 'تم تحديث رابط التواصل بنجاح',
        ]);
    }
}
