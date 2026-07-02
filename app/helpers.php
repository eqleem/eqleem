<?php

use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

if (! function_exists('theme_option')) {
    /**
     * @return array<string, mixed>|mixed
     */
    function theme_option(?string $key = null, mixed $default = null): mixed
    {
        $options = Context::get('theme_options', []);

        if ($key === null) {
            return is_array($options) ? $options : [];
        }

        return data_get($options, $key, $default);
    }
}

if (! function_exists('setCurrentTenant')) {
    function setCurrentTenant(Tenant $tenant): void
    {
        Context::addHidden('tenant', $tenant);
        Context::addHidden('tenant_id', $tenant->id);

        URL::defaults(['tenant' => $tenant->handle]);
    }
}

if (! function_exists('resolveCurrentTenant')) {
    function resolveCurrentTenant(): ?Tenant
    {
        $tenant = Context::getHidden('tenant');

        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $tenant = $user->currentTenant ?? $user->tenant;

        if ($tenant instanceof Tenant) {
            setCurrentTenant($tenant);
        }

        return $tenant;
    }
}

if (! function_exists('currentTenant')) {
    function currentTenant(): ?Tenant
    {
        return resolveCurrentTenant();
    }
}

if (! function_exists('tenant')) {
    function tenant($key = null, $default = null)
    {
        return data_get(resolveCurrentTenant(), $key, $default);
    }
}

if (! function_exists('currentTenantId')) {
    function currentTenantId(): ?int
    {
        $tenantId = Context::getHidden('tenant_id');

        if ($tenantId) {
            return (int) $tenantId;
        }

        return resolveCurrentTenant()?->id;
    }
}

if (! function_exists('user')) {
    function user($key = null)
    {
        $user = auth()->user();

        if ($key) {
            return data_get($user, $key, null);
        }

        return $user;
    }
}

if (! function_exists('generateKey')) {
    function generateKey($count = 16)
    {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes(ceil(16 / 2));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(ceil(16 / 2));
        } else {
            throw new Exception('No cryptographically secure random function available');
        }

        return substr(bin2hex($bytes), 0, $count);
    }
}

if (! function_exists('loadingIcon')) {
    function loadingIcon()
    {
        return '<div class="flex justify-center items-center p-3">
            <ui:icon name="loader-3" class="animate-spin text-pgray-500 w-10 h-10" />
        </div>';
    }
}

if (! function_exists('tenantView')) {
    function tenantView($view, $data = [])
    {
        return view()
            ->first(['tenant-theme::'.$view, 'default-tenant-theme::'.$view, $view], $data)
            ->layout('layouts.tenant');
    }
}

if (! function_exists('contentImageUrl')) {
    function contentImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('spaces')->url($path);
    }
}

if (! function_exists('legacyBlogCategoryIdsFromData')) {
    /**
     * @param  array<string, mixed>|null  $data
     * @return array<int, int>
     */
    function legacyBlogCategoryIdsFromData(?array $data): array
    {
        $categoryIds = data_get($data, 'category_ids');

        if (! is_array($categoryIds) || $categoryIds === []) {
            $legacyId = data_get($data, 'category_id');

            return filled($legacyId) ? [(int) $legacyId] : [];
        }

        return collect($categoryIds)
            ->filter(fn (mixed $id): bool => filled($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }
}

if (! function_exists('blogPostCategoryIds')) {
    /**
     * @return array<int, int>
     */
    function blogPostCategoryIds(Content $content): array
    {
        $content->migrateLegacyBlogCategoriesIfNeeded();

        return $content->taxonomiesOfType('blog_category')
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }
}
