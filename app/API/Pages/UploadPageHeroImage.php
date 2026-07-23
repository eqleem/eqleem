<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads the about-page hero image (stored in content data.hero_image).
 */
class UploadPageHeroImage
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxFileSizeKb = (int) (config('media-library.max_file_size') / 1024);

        return [
            'file' => ['required', 'image', 'max:'.$maxFileSizeKb],
        ];
    }

    /**
     * @return array{hero_image: string|null, hero_image_path: string|null}
     */
    public function handle(Tenant $tenant, string $uuid, UploadedFile $file): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);

        if ($content->template !== 'about') {
            abort(422, 'هذه الصفحة لا تدعم صورة البطل.');
        }

        $tenantUuid = (string) ($tenant->uuid ?? 'shared');
        $path = $file->storePublicly('tenant-media/'.$tenantUuid.'/pages/'.$content->id.'/hero', 'spaces');

        $data = is_array($content->data) ? $content->data : [];
        $data['hero_image'] = $path;

        $content->update(['data' => $data]);

        return [
            'hero_image' => contentImageUrl($path) ?? $path,
            'hero_image_path' => $path,
        ];
    }

    /**
     * @return array{hero_image: string|null, hero_image_path: string|null}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        return $this->handle($this->currentDashboardTenant($request), $uuid, $file);
    }

    /**
     * @param  array{hero_image: string|null, hero_image_path: string|null}  $result
     * @return array{data: array{hero_image: string|null, hero_image_path: string|null}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
