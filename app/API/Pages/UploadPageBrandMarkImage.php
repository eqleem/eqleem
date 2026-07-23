<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Models\Tenant;
use App\Support\BlockBrandMark;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads an image brand mark for about-page features.
 */
class UploadPageBrandMarkImage
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
     * @return array{brand_mark: array{type: string, value: string, color: string, url: string|null, path: string}}
     */
    public function handle(Tenant $tenant, string $uuid, UploadedFile $file): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);

        if ($content->template !== 'about') {
            abort(422, 'هذه الصفحة لا تدعم رفع أيقونات المزايا.');
        }

        $tenantUuid = (string) ($tenant->uuid ?? 'shared');
        $path = $file->storePublicly(
            'tenant-media/'.$tenantUuid.'/pages/'.$content->id.'/features',
            'spaces',
        );

        $stored = [
            'type' => 'image',
            'value' => '',
            'color' => '',
            'path' => $path,
        ];

        $editor = BlockBrandMark::forEditor($stored);

        return [
            'brand_mark' => [
                'type' => 'image',
                'value' => '',
                'color' => '',
                'path' => $path,
                'url' => $editor['url'] ?? contentImageUrl($path),
            ],
        ];
    }

    /**
     * @return array{brand_mark: array{type: string, value: string, color: string, url: string|null, path: string}}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        return $this->handle($this->currentDashboardTenant($request), $uuid, $file);
    }

    /**
     * @param  array{brand_mark: array{type: string, value: string, color: string, url: string|null, path: string}}  $result
     * @return array{data: array{brand_mark: array{type: string, value: string, color: string, url: string|null, path: string}}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
