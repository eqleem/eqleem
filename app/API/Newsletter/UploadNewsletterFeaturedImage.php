<?php

namespace App\API\Newsletter;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Newsletter\Concerns\ResolvesNewsletter;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads the newsletter featured image (stored in content data.image).
 */
class UploadNewsletterFeaturedImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesNewsletter;

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
     * @return array{featured_image: string|null}
     */
    public function handle(Tenant $tenant, string $uuid, UploadedFile $file): array
    {
        setCurrentTenant($tenant);

        $content = $this->findNewsletter($uuid);
        $tenantUuid = (string) ($tenant->uuid ?? 'shared');
        $path = $file->storePublicly('tenant-media/'.$tenantUuid.'/newsletter', 'spaces');

        $data = $content->data ?? [];
        $data['image'] = $path;

        $content->update(['data' => $data]);

        return [
            'featured_image' => contentImageUrl($path) ?? $path,
        ];
    }

    /**
     * @return array{featured_image: string|null}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        return $this->handle($this->currentDashboardTenant($request), $uuid, $file);
    }

    /**
     * @param  array{featured_image: string|null}  $result
     * @return array{data: array{featured_image: string|null}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
