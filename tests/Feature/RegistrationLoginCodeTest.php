<?php

use App\Actions\SendRegistrationLink;
use App\Actions\VerifyRegistrationCode;
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
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);

    Http::fake([
        '*' => Http::response(['data' => ['id' => 'traffic-register']], 201),
    ]);
});

it('sends a registration login code with the magic link email', function () {
    Mail::fake();

    SendRegistrationLink::run('owner@example.com');

    Mail::assertQueued(RegistrationLink::class, function (RegistrationLink $mail) {
        return $mail->hasTo('owner@example.com')
            && strlen($mail->code) === 6
            && filled($mail->url);
    });

    expect(DB::table('registration_tokens')->where('email', 'owner@example.com')->value('code'))->not->toBeNull();
});

it('creates an account and logs in with a valid registration code', function () {
    Mail::fake();

    $email = 'code-owner@example.com';
    $code = '654321';

    DB::table('registration_tokens')->insert([
        'email' => $email,
        'token' => hash('sha256', str_repeat('a', 64)),
        'code' => hash('sha256', $code),
        'created_at' => now(),
    ]);

    $data = VerifyRegistrationCode::run($email, $code);

    expect($data['user'])->not->toBeNull()
        ->and($data['user']->email)->toBe($email)
        ->and($data['tenant'])->not->toBeNull()
        ->and(Tenant::where('user_id', $data['user']->id)->exists())->toBeTrue();

    Mail::assertQueued(WelcomeUser::class);
});

it('logs an existing user in with a valid registration code', function () {
    $user = User::factory()->create([
        'email' => 'existing-code@example.com',
        'current_tenant_id' => null,
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة موجودة',
        'handle' => 'existing-code-page',
        'email' => $user->email,
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $code = '112233';

    DB::table('registration_tokens')->insert([
        'email' => $user->email,
        'token' => hash('sha256', str_repeat('b', 64)),
        'code' => hash('sha256', $code),
        'created_at' => now(),
    ]);

    $data = VerifyRegistrationCode::run($user->email, $code);

    $user->refresh();

    expect($data['user']->id)->toBe($user->id)
        ->and((int) $user->current_tenant_id)->toBe($tenant->id);
});

it('rejects an invalid registration code', function () {
    DB::table('registration_tokens')->insert([
        'email' => 'bad-code@example.com',
        'token' => hash('sha256', str_repeat('c', 64)),
        'code' => hash('sha256', '111111'),
        'created_at' => now(),
    ]);

    VerifyRegistrationCode::run('bad-code@example.com', '999999');
})->throws(ValidationException::class);

it('logs in through the livewire register-login component with a code', function () {
    Mail::fake();

    $email = 'livewire-owner@example.com';
    $code = '445566';

    Livewire::test('auth::register-login')
        ->set('email', $email)
        ->call('submit')
        ->assertSet('otpStep', true)
        ->assertSet('codeSent', true);

    DB::table('registration_tokens')->where('email', $email)->update([
        'code' => hash('sha256', $code),
    ]);

    Livewire::test('auth::register-login')
        ->set('email', $email)
        ->set('otpStep', true)
        ->set('code', $code)
        ->call('verifyCode')
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', $email)->first();

    expect($user)->not->toBeNull()
        ->and(auth()->check())->toBeTrue()
        ->and(auth()->id())->toBe($user->id);
});
