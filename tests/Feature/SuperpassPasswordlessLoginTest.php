<?php

use App\Actions\SendSuperpassLoginCode;
use App\Actions\VerifySuperpassLoginCode;
use App\Filament\Pages\Auth\Login;
use App\Mail\SuperpassLoginCode;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('superpass'));
    Mail::fake();
});

it('sends a login code email instead of logging in directly', function () {
    $user = User::factory()->create([
        'email' => 'admin@eqleem.com',
        'name' => 'Admin',
    ]);

    Livewire::test(Login::class)
        ->assertFormFieldExists('email')
        ->assertFormFieldDoesNotExist('password')
        ->assertFormFieldIsHidden('code')
        ->fillForm([
            'email' => 'admin@eqleem.com',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertSet('otpStep', true)
        ->assertFormFieldIsVisible('code')
        ->assertNotified();

    $this->assertGuest();

    Mail::assertQueued(SuperpassLoginCode::class, function (SuperpassLoginCode $mail) use ($user): bool {
        return $mail->hasTo($user->email)
            && strlen($mail->code) === 6;
    });

    expect(DB::table('superpass_login_codes')->where('email', 'admin@eqleem.com')->exists())->toBeTrue();
});

it('logs in after verifying the emailed login code', function () {
    $user = User::factory()->create([
        'email' => 'admin@eqleem.com',
    ]);

    $component = Livewire::test(Login::class)
        ->fillForm([
            'email' => 'admin@eqleem.com',
        ])
        ->call('authenticate')
        ->assertSet('otpStep', true);

    /** @var SuperpassLoginCode $mail */
    $mail = Mail::queued(SuperpassLoginCode::class)->first();

    $component
        ->fillForm([
            'email' => 'admin@eqleem.com',
            'code' => $mail->code,
            'remember' => true,
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect(Filament::getUrl());

    $this->assertAuthenticatedAs($user);
    expect(DB::table('superpass_login_codes')->where('email', 'admin@eqleem.com')->exists())->toBeFalse();
});

it('rejects unknown emails when requesting a code', function () {
    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'unknown@example.com',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    $this->assertGuest();
    Mail::assertNothingQueued();
});

it('rejects production users who cannot access the panel', function () {
    app()->detectEnvironment(fn (): string => 'production');

    User::factory()->create([
        'email' => 'random@example.com',
    ]);

    expect(fn () => SendSuperpassLoginCode::run('random@example.com'))
        ->toThrow(ValidationException::class);

    Mail::assertNothingQueued();
});

it('rejects an invalid login code', function () {
    User::factory()->create([
        'email' => 'admin@eqleem.com',
    ]);

    DB::table('superpass_login_codes')->insert([
        'email' => 'admin@eqleem.com',
        'code' => hash('sha256', '123456'),
        'expires_at' => now()->addMinutes(10),
        'created_at' => now(),
    ]);

    expect(fn () => VerifySuperpassLoginCode::run('admin@eqleem.com', '999999'))
        ->toThrow(ValidationException::class);

    $this->assertGuest();
});
