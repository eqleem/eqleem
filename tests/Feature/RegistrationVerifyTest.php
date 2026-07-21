<?php

use App\Actions\SendRegistrationLink;
use App\Mail\RegistrationLink;
use App\Mail\WelcomeUser;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);

    Http::fake([
        '*' => Http::response(['data' => ['id' => 'traffic-register']], 201),
    ]);
});

it('creates an account and logs the user in from the registration email link', function () {
    Mail::fake();

    $email = 'new-owner@example.com';

    SendRegistrationLink::run($email);

    Mail::assertQueued(RegistrationLink::class, function (RegistrationLink $mail) use ($email) {
        return $mail->hasTo($email) && strlen($mail->code) === 6 && filled($mail->url);
    });

    $token = str_repeat('a', 64);

    DB::table('registration_tokens')->where('email', $email)->update([
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    Livewire::withQueryParams(['email' => $email])
        ->test('auth::register-verify', ['token' => $token])
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', $email)->first();

    expect($user)->not->toBeNull()
        ->and($user->current_tenant_id)->not->toBeNull()
        ->and(Tenant::where('user_id', $user->id)->exists())->toBeTrue()
        ->and(auth()->check())->toBeTrue()
        ->and(auth()->id())->toBe($user->id);

    Mail::assertQueued(WelcomeUser::class);
});

it('logs an existing user in from the registration email link', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'existing-owner@example.com',
        'current_tenant_id' => null,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة موجودة',
        'handle' => 'existing-page',
        'email' => $user->email,
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $token = str_repeat('b', 64);

    DB::table('registration_tokens')->insert([
        'email' => $user->email,
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    Livewire::withQueryParams(['email' => $user->email])
        ->test('auth::register-verify', ['token' => $token])
        ->assertRedirect(route('dashboard'));

    $user->refresh();

    expect((int) $user->current_tenant_id)->toBe($tenant->id)
        ->and(auth()->id())->toBe($user->id);
});
