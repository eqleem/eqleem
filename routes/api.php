<?php

use App\API\Dashboard\GetDashboardContext;
use App\API\Settings\UpdateTenantCustomDomain;
use App\API\Settings\UpdateTenantHandle;
use App\API\User\UpdateAccountProfile;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/context', GetDashboardContext::class)
    ->name('api.dashboard.context');

Route::put('/account/profile', UpdateAccountProfile::class)
    ->name('api.account.profile.update');

Route::put('/settings/domain/handle', UpdateTenantHandle::class)
    ->name('api.settings.domain.handle');

Route::put('/settings/domain/custom', UpdateTenantCustomDomain::class)
    ->name('api.settings.domain.custom');
