<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Removes the about-page hero image.
 */
class DeletePageHeroImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPage;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    /**
     * @return array{hero_image: null, hero_image_path: null}
     */
    public function handle(Tenant $tenant, string $uuid): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);

        if ($content->template !== 'about') {
            abort(422, 'هذه الصفحة لا تدعم صورة البطل.');
        }

        $data = is_array($content->data) ? $content->data : [];
        $data['hero_image'] = null;

        $content->update(['data' => $data]);

        return [
            'hero_image' => null,
            'hero_image_path' => null,
        ];
    }

    /**
     * @return array{hero_image: null, hero_image_path: null}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    /**
     * @param  array{hero_image: null, hero_image_path: null}  $result
     * @return array{data: array{hero_image: null, hero_image_path: null}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
