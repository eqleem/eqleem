<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use MeShaon\RequestAnalytics\Models\RequestAnalytics;

class RequestAnalyticsObserver
{
    public function creating(RequestAnalytics $requestAnalytics): void
    {
        if (tenant() && is_null($requestAnalytics->tenant_id)) {
            $requestAnalytics->tenant_id = tenant()->id;
        }

        $clientId = Auth::guard('client')->id();

        if ($clientId && is_null($requestAnalytics->client_id)) {
            $requestAnalytics->client_id = $clientId;
        }
    }
}
