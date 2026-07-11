<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class DashboardPlanPaymentCallback
{
    use AsAction;

    public function handle(Request $request)
    {
        return PaymentCallback::make()->handle(
            $request,
            successRedirect: '/dashboard/plan?status=success',
            failureRedirect: '/dashboard/plan?status=error',
        );
    }
}
