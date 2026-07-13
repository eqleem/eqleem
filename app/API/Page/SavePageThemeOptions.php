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

            if (in_array($type, ['upload-single-image', 'upload-cover'], true)) {
                $upload = $uploads[$key] ?? null;

                if ($upload instanceof UploadedFile) {
                    $merged[$key] = $tenant->uploadThemeOptionMedia($themeId, $key, $upload);
                } else {
                    $incoming = data_get($options, $key);
                    $fallback = data_get($saved, $key, $field['default'] ?? '');

                    if ($incoming === '__clear__') {
                        $merged[$key] = '';
                    } elseif (filled($incoming)) {
                        if ($type === 'upload-cover' && ! $this->isAllowedCoverValue($incoming)) {
                            abort(422, __('Invalid cover value.'));
                        }

                        $merged[$key] = $incoming;
                    } else {
                        // Preserve the saved media path when the client omits the option
                        // without sending a replacement upload.
                        $merged[$key] = $fallback;
                    }
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
        /** @var array{theme_id: int, options?: array<string, mixed>} $validated */
        $validated = $request->validated();

        /** @var array<string, UploadedFile|null> $uploads */
        $uploads = $request->file('uploads', []);

        if (! is_array($uploads)) {
            $uploads = [];
        }

        return $this->handle(
            $this->currentDashboardTenant($request),
            (int) $validated['theme_id'],
            $validated['options'] ?? [],
            $uploads,
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

    private function isAllowedCoverValue(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        if (str_starts_with($value, 'color:')) {
            return (bool) preg_match('/^color:#[0-9a-fA-F]{3,8}$/', $value);
        }

        if (str_starts_with($value, 'gradient:')) {
            $css = substr($value, strlen('gradient:'));

            return (bool) preg_match('/^(linear|radial)-gradient\([a-zA-Z0-9#%.,\s()\-]+\)$/', $css);
        }

        if (str_starts_with($value, 'https://') || str_starts_with($value, 'http://')) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        }

        // Stored media relative path (no schemes).
        return (bool) preg_match('#^[A-Za-z0-9_./-]+$#', $value)
            && ! str_contains($value, '..');
    }
}
