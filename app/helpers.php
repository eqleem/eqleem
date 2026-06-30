<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\URL;

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
