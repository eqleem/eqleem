<?php

use App\Mail\ContactMessage;
use App\Models\Tenant;

it('renders an arabic rtl contact message email with manage page button', function () {
    $tenant = new Tenant([
        'name' => 'متجر أحمد',
        'handle' => 'ahmad-store',
    ]);

    $mail = new ContactMessage(
        contact: [
            'name' => 'سارة علي',
            'email' => 'sara@example.com',
            'phone' => '0501234567',
            'address' => 'الرياض',
            'subject' => 'رسالة من نموذج اتصل بنا',
            'message' => "مرحباً،\nأرغب بمعرفة المزيد عن خدماتكم.",
        ],
        tenant: $tenant,
        managePageUrl: 'https://example.test/admin/manage-page',
    );

    $html = $mail->render();

    expect($html)
        ->toContain('dir="rtl"')
        ->toContain('lang="ar"')
        ->toContain('متجر أحمد')
        ->toContain('وصلتكم رسالة جديدة من نموذج اتصل بنا')
        ->toContain('رسالة من نموذج اتصل بنا')
        ->toContain('سارة علي')
        ->toContain('sara@example.com')
        ->toContain('0501234567')
        ->toContain('الرياض')
        ->toContain('أرغب بمعرفة المزيد عن خدماتكم.')
        ->toContain('https://example.test/admin/manage-page')
        ->toContain('إدارة الصفحة من لوحة التحكم');
});

it('uses tenant name in the email subject', function () {
    $tenant = new Tenant([
        'name' => 'صفحة سارة',
        'handle' => 'sara-page',
    ]);

    $mail = new ContactMessage(
        contact: [
            'name' => 'أحمد',
            'email' => 'ahmad@example.com',
            'subject' => 'رسالة من نموذج اتصل بنا',
            'message' => 'مرحباً',
        ],
        tenant: $tenant,
        managePageUrl: 'https://example.test/admin/manage-page',
    );

    expect($mail->envelope()->subject)->toBe('رسالة جديدة — صفحة سارة — رسالة من نموذج اتصل بنا');
});
