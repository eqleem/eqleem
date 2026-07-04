<?php

namespace App\Observers;

use MeShaon\RequestAnalytics\Models\RequestAnalytics;
use Illuminate\Database\Eloquent\Builder;
    
class RequestAnalyticsObserver
{
    public function created(RequestAnalytics $requestAnalytics): void
    {
        if(tenant()) {
            $requestAnalytics->tenant_id = tenant()->id;
            $requestAnalytics->saveQuietly();
        }
    }

    protected static function bootedTenantRequestAnalytics(RequestAnalytics $model) 
    {
      
        $model::addGlobalScope('tenant_request_analytics', function (Builder $builder) {
            $builder->where('tenant_id', tenant('id') ?? null);   
        });
    } 
}
