<?php

use App\Actions\CreateTenant;
use App\Mail\WelcomeUser;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

it('queues a welcome email after tenant registration', function () {
    Mail::fake();

    Http::fake([
        '*' => Http::response(['data' => ['id' => 'traffic-welcome']], 201),
    ]);

    $user = User::factory()->create([
        'name' => 'أحمد محمد',
        'email' => 'ahmad@example.com',
    ]);

    $tenant = CreateTenant::run([
        'tenant_name' => 'متجر أحمد',
        'tenant_handle' => 'ahmad-store',
        'email' => 'ahmad@example.com',
        'user_id' => $user->id,
    ]);

    Mail::assertQueued(WelcomeUser::class, function (WelcomeUser $mail) use ($user, $tenant) {
        return $mail->hasTo('ahmad@example.com')
            && $mail->user->is($user)
            && $mail->tenant->is($tenant)
            && $mail->pageUrl === route('tenant.home', $tenant->handle)
            && $mail->dashboardUrl === route('admin.home')
            && $mail->managePageUrl === route('admin.page.home');
    });
});

it('renders the welcome email with page and dashboard links', function () {
    $user = User::factory()->make([
        'name' => 'سارة علي',
        'email' => 'sara@example.com',
    ]);

    $tenant = new Tenant([
        'name' => 'صفحة سارة',
        'handle' => 'sara-page',
    ]);

    $mail = new WelcomeUser(
        user: $user,
        tenant: $tenant,
        pageUrl: 'https://sara-page.example.test',
        dashboardUrl: 'https://example.test/admin',
        managePageUrl: 'https://example.test/admin/manage-page',
    );

    $html = $mail->render();

    expect($html)
        ->toContain('سارة علي')
        ->toContain('صفحة سارة')
        ->toContain('https://sara-page.example.test')
        ->toContain('https://example.test/admin')
        ->toContain('ادخل لوحة التحكم وابدأ الآن');
});
