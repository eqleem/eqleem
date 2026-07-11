<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\UnitRental\Concerns\ResolvesUnitRental;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads an image to a store project's gallery.
 */
class UploadUnitRentalImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesUnitRental;

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
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function handle(Tenant $tenant, string $uuid, UploadedFile $file): array
    {
        setCurrentTenant($tenant);

        $content = $this->findUnitRental($uuid);

        $count = $content->getMedia('unit-media')->count();

        if ($count >= 20) {
            abort(422, __('Maximum of :count images allowed.', ['count' => 20]));
        }

        $content
            ->addMedia($file)
            ->usingFileName(md5($file->getClientOriginalName()).'.'.$file->getClientOriginalExtension())
            ->toMediaCollection('unit-media');

        return [
            'images' => $content->fresh()->unitImages(),
        ];
    }

    /**
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return $this->handle($tenant, $uuid, $file);
    }

    /**
     * @param  array{images: list<array{id: int, url: string}>}  $result
     * @return array{data: array{images: list<array{id: int, url: string}>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
