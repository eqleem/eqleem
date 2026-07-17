<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\WelcomeWidgetResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\PageCompletion;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves welcome-widget basic info (page name, logo, header bio).
 */
class UpdateWelcomeBasicInfo
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'bio' => ['nullable', 'string', 'max:250'],
            'logo' => ['nullable', 'image', 'max:15024'],
        ];
    }

    /**
     * @param  array{name: string, bio?: string|null, logo?: UploadedFile|null}  $data
     * @return array<string, mixed>
     */
    public function handle(User $user, Tenant $tenant, array $data, PageCompletion $pageCompletion): array
    {
        $tenant->name = $data['name'];
        $tenant->save();

        $profile = app(TenantProfileService::class);
        $profile->saveBio($tenant, (string) ($data['bio'] ?? ''));

        $logo = $data['logo'] ?? null;

        if ($logo instanceof UploadedFile) {
            $path = $logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
            $profile->saveLogo($tenant, $path);
        }

        return GetWelcomeWidget::make()->handle($user, $tenant->fresh(), $pageCompletion);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, PageCompletion $pageCompletion): array
    {
        /** @var User $user */
        $user = $request->user();
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{name: string, bio?: string|null, logo?: UploadedFile|null} $validated */
        $validated = $request->validated();

        return $this->handle($user, $tenant, $validated, $pageCompletion);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): WelcomeWidgetResource
    {
        return (new WelcomeWidgetResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
