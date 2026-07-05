<?php

namespace App\Actions;

use App\Mail\WelcomeUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendWelcomeEmail
{
    use AsAction;

    public function handle(Tenant $tenant): void
    {
        $tenant->loadMissing('user');

        $user = $tenant->user;

        if (! $user instanceof User || blank($user->email)) {
            return;
        }

        Mail::to($user->email)->queue(new WelcomeUser(
            user: $user,
            tenant: $tenant,
            pageUrl: $tenant->url,
            dashboardUrl: route('admin.home'),
            managePageUrl: route('admin.page.home'),
        ));
    }
}
