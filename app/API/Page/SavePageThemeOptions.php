<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageThemes;
use App\Http\Resources\PageDesignResource;
use App\Models\Tenant;
use App\Support\TenantThemeOptions;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Saves theme customization options for a theme.
 */
class SavePageThemeOptions
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
            'options' => ['sometimes', 'array'],
            'uploads' => ['sometimes', 'array'],
            'uploads.*' => ['nullable', 'image', 'max:15024'],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, UploadedFile|null>  $uploads
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $themeId, array $options, array $uploads): array
    {
        setCurrentTenant($tenant);

        $theme = $this->findPublicTheme($themeId);

        abort_unless($theme !== null, 404);

        $schema = app(TenantThemeOptions::class)->schemaForTheme($theme->slug);

        abort_if($schema === [], 422);

        $saved = $tenant->themeSettingsFor($themeId);
        $merged = [];

        foreach ($schema as $key => $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'upload-single-image') {
                $upload = $uploads[$key] ?? null;

                if ($upload instanceof UploadedFile) {
                    $merged[$key] = $tenant->uploadThemeOptionMedia($themeId, $key, $upload);
                } else {
                    $incoming = data_get($options, $key);
                    $fallback = data_get($saved, $key, $field['default'] ?? '');

                    // Preserve the saved media path when the client omits/clears the option
                    // without sending a replacement upload.
                    $merged[$key] = filled($incoming) ? $incoming : $fallback;
                }

                continue;
            }

            $merged[$key] = data_get($options, $key, data_get($saved, $key, $field['default'] ?? ''));
        }

        $tenant->saveThemeSettingsFor($themeId, $merged);

        return GetPageDesign::make()->handle($tenant->fresh(), $themeId);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        /** @var array{theme_id: int, options?: array<string, mixed>, uploads?: array<string, UploadedFile|null>} $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            (int) $validated['theme_id'],
            $validated['options'] ?? [],
            $validated['uploads'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PageDesignResource
    {
        return (new PageDesignResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
