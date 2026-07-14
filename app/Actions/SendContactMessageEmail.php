<?php

namespace App\Actions;

use App\Mail\ContactMessage;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendContactMessageEmail
{
    use AsAction;

    /**
     * @param  array{name?: string, email?: string, phone?: string, address?: string, subject?: string, message?: string}  $contact
     */
    public function handle(Tenant $tenant, array $contact): void
    {
        $recipient = $this->recipientEmail($tenant);

        if (blank($recipient)) {
            return;
        }

        Mail::to($recipient)->queue(new ContactMessage(
            contact: [
                'name' => (string) ($contact['name'] ?? ''),
                'email' => (string) ($contact['email'] ?? ''),
                'phone' => (string) ($contact['phone'] ?? ''),
                'address' => (string) ($contact['address'] ?? ''),
                'subject' => filled($contact['subject'] ?? null)
                    ? (string) $contact['subject']
                    : 'رسالة من نموذج اتصل بنا',
                'message' => (string) ($contact['message'] ?? ''),
            ],
            tenant: $tenant,
            managePageUrl: route('admin.page.home'),
        ));
    }

    protected function recipientEmail(Tenant $tenant): ?string
    {
        if (filled($tenant->email)) {
            return (string) $tenant->email;
        }

        $contactEmail = (string) (app(TenantProfileService::class)->contact($tenant)['email'] ?? '');

        if (filled($contactEmail)) {
            return $contactEmail;
        }

        $tenant->loadMissing('user');

        if (filled($tenant->user?->email)) {
            return (string) $tenant->user->email;
        }

        return null;
    }
}
