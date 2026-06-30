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

if (! function_exists('currentTenant')) {
    function currentTenant(): ?Tenant
    {
        return Context::getHidden('tenant');
    }
}

if (! function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        $tenant = Context::getHidden('tenant');
        return $tenant ?: null;
    }
}

if (! function_exists('currentTenantId')) {
    function currentTenantId(): ?int
    {
        return Context::getHidden('tenant_id');
    }
}


if (!function_exists('generateKey')) {
    function generateKey($count = 16)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil(16 / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil(16 / 2));
        } else {
            throw new \Exception("No cryptographically secure random function available");
        }

        return substr(bin2hex($bytes), 0, $count);
    }
}
