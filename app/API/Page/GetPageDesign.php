<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageThemes;
use App\Http\Resources\PageDesignResource;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns available themes and customization options for the selected theme.
 */
class GetPageDesign
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
            'theme_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, ?int $themeId = null): array
    {
        setCurrentTenant($tenant);

        $tenantThemeId = $tenant->theme_id;
        $themes = $this->mappedThemes($tenantThemeId);

        $selectedThemeId = $themeId
            ?? $tenantThemeId
            ?? $themes->first()['id'] ?? null;

        if ($selectedThemeId && ! $themes->contains(fn (array $theme): bool => $theme['id'] === $selectedThemeId)) {
            $selectedThemeId = $themes->first()['id'] ?? null;
        }

        $selectedTheme = $themes->firstWhere('id', $selectedThemeId);
        $optionsSchema = [];
        $options = [];
        $optionPreviews = [];

        if ($selectedThemeId) {
            $theme = $this->findPublicTheme($selectedThemeId);

            if ($theme) {
                $payload = $this->themeOptionsPayload(
                    $theme,
                    $tenant->themeSettingsFor($selectedThemeId),
                );

                $optionsSchema = $payload['schema'];
                $options = $payload['options'];
                $optionPreviews = $payload['previews'];
            }
        }

        return [
            'themes' => $themes->values()->all(),
            'selected_theme_id' => $selectedThemeId,
            'tenant_theme_id' => $tenantThemeId,
            'selected_theme' => $selectedTheme,
            'options_schema' => $optionsSchema,
            'options' => $options,
            'option_previews' => $optionPreviews,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        $themeId = $request->validated('theme_id');

        return $this->handle(
            $this->currentDashboardTenant($request),
            $themeId !== null ? (int) $themeId : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PageDesignResource
    {
        return new PageDesignResource($payload);
    }
}
