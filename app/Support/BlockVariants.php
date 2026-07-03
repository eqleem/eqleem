<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

class BlockVariants
{
    /**
     * @return array<string, string>
     */
    public function optionsFor(string $blockType, ?Tenant $tenant = null): array
    {
        $themeSlug = $this->themeSlug($tenant);

        $variants = $this->discover($blockType, $themeSlug);

        if ($variants === [] && $themeSlug !== 'default') {
            $variants = $this->discover($blockType, 'default');
        }

        return collect($variants)
            ->mapWithKeys(fn (string $variant): array => [
                $variant => $this->labelFor($variant),
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    public function discover(string $blockType, string $themeSlug): array
    {
        $directory = public_path("themes/{$themeSlug}/blocks/{$blockType}");

        if (! is_dir($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->map(fn (SplFileInfo $file): string => Str::beforeLast($file->getFilename(), '.blade.php'))
            ->sort()
            ->values()
            ->all();
    }

    protected function labelFor(string $variant): string
    {
        return Str::headline(str_replace('-', ' ', $variant));
    }

    protected function themeSlug(?Tenant $tenant = null): string
    {
        $tenant = $tenant ?? currentTenant();

        if (! $tenant) {
            return 'default';
        }

        $tenant->loadMissing('theme');

        return $tenant->theme?->slug ?? 'default';
    }
}
