<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageThemes;
use App\Http\Resources\PageDesignResource;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Sets the tenant's default (active) theme.
 */
class SetDefaultPageTheme
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageThemes;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'theme_id' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $themeId): array
    {
        setCurrentTenant($tenant);

        $theme = $this->findPublicTheme($themeId);

        abort_unless($theme !== null, 404);

        $tenant->update(['theme_id' => $themeId]);

        return GetPageDesign::make()->handle($tenant->fresh(), $themeId);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        /** @var array{theme_id: int} $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            (int) $validated['theme_id'],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PageDesignResource
    {
        return (new PageDesignResource($payload))
            ->additional([
                'message' => 'تم تعيين القالب الافتراضي بنجاح.',
            ]);
    }
}
