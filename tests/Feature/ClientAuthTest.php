<?php

use App\Actions\SendClientLoginCode;
use App\Actions\VerifyClientLoginCode;
use App\Mail\ClientLoginCode;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
});

function createClientAuthTenant(string $handle = 'client-auth-tenant'): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    return Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Client Auth Tenant',
        'handle' => $handle,
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);
}

it('sends a login code and magic link to the client email', function () {
    Mail::fake();

    $tenant = createClientAuthTenant();

    SendClientLoginCode::run('client@example.com', $tenant->id);

    Mail::assertQueued(ClientLoginCode::class, function (ClientLoginCode $mail) use ($tenant) {
        return $mail->hasTo('client@example.com')
            && $mail->tenant->is($tenant)
            && strlen($mail->code) === 6
            && str_contains($mail->loginUrl, '/client/auth/email')
            && str_contains($mail->loginUrl, 'signature=');
    });

    expect(DB::table('client_login_codes')->where('tenant_id', $tenant->id)->count())->toBe(1);
});

it('creates a client, links tenant, and logs in with a valid code', function () {
    $tenant = createClientAuthTenant();
    setCurrentTenant($tenant);

    $code = '654321';

    DB::table('client_login_codes')->insert([
        'tenant_id' => $tenant->id,
        'email' => 'client@example.com',
        'code' => hash('sha256', $code),
        'expires_at' => now()->addMinutes(10),
        'created_at' => now(),
    ]);

    VerifyClientLoginCode::run('client@example.com', $code, $tenant->id);

    $client = Client::withoutGlobalScope('tenantable')->where('email', 'client@example.com')->first();

    expect(auth('client')->check())->toBeTrue()
        ->and(auth('client')->id())->toBe($client->id)
        ->and($client->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue()
        ->and($client->profileForTenant($tenant->id)['email'])->toBe('client@example.com');
});

it('links an existing client to a new tenant through tenantables', function () {
    $tenantA = createClientAuthTenant('tenant-a');
    $tenantB = createClientAuthTenant('tenant-b');

    $client = Client::withoutGlobalScope('tenantable')->create([
        'name' => 'Existing Client',
        'email' => 'existing@example.com',
        'tenant_id' => $tenantA->id,
    ]);

    $client->tenants()->attach($tenantA->id, [
        'active' => true,
        'meta' => [
            'name' => 'Tenant A Name',
            'email' => 'existing@example.com',
        ],
    ]);

    setCurrentTenant($tenantB);

    $code = '112233';

    DB::table('client_login_codes')->insert([
        'tenant_id' => $tenantB->id,
        'email' => 'existing@example.com',
        'code' => hash('sha256', $code),
        'expires_at' => now()->addMinutes(10),
        'created_at' => now(),
    ]);

    VerifyClientLoginCode::run('existing@example.com', $code, $tenantB->id);

    $client->refresh();

    expect($client->tenants()->where('tenants.id', $tenantA->id)->exists())->toBeTrue()
        ->and($client->tenants()->where('tenants.id', $tenantB->id)->exists())->toBeTrue()
        ->and($client->displayName($tenantA->id))->toBe('Tenant A Name')
        ->and($client->displayName($tenantB->id))->toBe('existing');
});

it('rejects an invalid login code', function () {
    $tenant = createClientAuthTenant();

    DB::table('client_login_codes')->insert([
        'tenant_id' => $tenant->id,
        'email' => 'client@example.com',
        'code' => hash('sha256', '111111'),
        'expires_at' => now()->addMinutes(10),
        'created_at' => now(),
    ]);

    VerifyClientLoginCode::run('client@example.com', '999999', $tenant->id);
})->throws(ValidationException::class);

it('logs in through the livewire client login component', function () {
    Mail::fake();

    $tenant = createClientAuthTenant();
    setCurrentTenant($tenant);

    Livewire::test('tenant.client-login')
        ->set('email', 'livewire@example.com')
        ->call('sendCode')
        ->assertSet('otpStep', true)
        ->assertSet('codeSent', true)
        ->assertSee('الدخول بواسطة Gmail')
        ->assertSee(route('tenant.client.auth.social', ['tenant' => $tenant->handle, 'provider' => 'google']), false)
        ->assertDontSee('GitHub');

    $mail = Mail::queued(ClientLoginCode::class)->first();
    $code = $mail->code;

    Livewire::test('tenant.client-login')
        ->set('email', 'livewire@example.com')
        ->set('code', $code)
        ->set('otpStep', true)
        ->call('verifyCode')
        ->assertDispatched('client-authenticated');

    $client = auth('client')->user();

    expect(auth('client')->check())->toBeTrue()
        ->and($client->email)->toBe('livewire@example.com')
        ->and($client->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue();
});

it('logs in through the signed email magic link and redirects home', function () {
    $tenant = createClientAuthTenant();
    setCurrentTenant($tenant);

    $code = '445566';
    $email = 'magic@example.com';

    DB::table('client_login_codes')->insert([
        'tenant_id' => $tenant->id,
        'email' => $email,
        'code' => hash('sha256', $code),
        'expires_at' => now()->addMinutes(10),
        'created_at' => now(),
    ]);

    $loginUrl = URL::temporarySignedRoute(
        'tenant.client.auth.email',
        now()->addMinutes(10),
        [
            'tenant' => $tenant->handle,
            'email' => $email,
            'code' => $code,
        ],
    );

    $this->get($loginUrl)
        ->assertRedirect(route('tenant.home', ['tenant' => $tenant->handle]));

    $client = Client::withoutGlobalScope('tenantable')->where('email', $email)->first();

    expect(auth('client')->check())->toBeTrue()
        ->and(auth('client')->id())->toBe($client->id)
        ->and($client->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue();
});

it('rejects an unsigned magic login link', function () {
    $tenant = createClientAuthTenant();

    $this->get(route('tenant.client.auth.email', [
        'tenant' => $tenant->handle,
        'email' => 'unsigned@example.com',
        'code' => '123456',
    ]))->assertForbidden();
});

it('logs a client in with Google using the shared auth callback', function () {
    $tenant = createClientAuthTenant();

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-client-1',
        'name' => 'Client Google',
        'nickname' => 'clientgoogle',
        'email' => 'client.google@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/client.png',
    ]));

    $this->withSession(['client_auth_tenant_id' => $tenant->id])
        ->get('/auth/google/callback')
        ->assertRedirect(route('tenant.home', ['tenant' => $tenant->handle]));

    $client = Client::withoutGlobalScope('tenantable')
        ->where('email', 'client.google@example.com')
        ->first();

    expect(auth('client')->check())->toBeTrue()
        ->and(auth('client')->id())->toBe($client->id)
        ->and(auth()->check())->toBeFalse()
        ->and($client->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue();
});

it('allows the same Google account as a web user and as a tenant client', function () {
    $tenant = createClientAuthTenant('shared-google-tenant');

    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'sharedgoogle@example.com',
        'name' => 'Shared Google',
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-shared-1',
        'name' => 'Shared Google',
        'nickname' => 'sharedgoogle',
        'email' => 'sharedgoogle@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/shared.png',
    ]));

    $this->actingAs($user)
        ->withSession(['client_auth_tenant_id' => $tenant->id])
        ->get('/auth/google/callback')
        ->assertRedirect(route('tenant.home', ['tenant' => $tenant->handle]));

    $client = Client::withoutGlobalScope('tenantable')
        ->where('email', 'sharedgoogle@example.com')
        ->first();

    expect(auth()->id())->toBe($user->id)
        ->and(auth('client')->check())->toBeTrue()
        ->and(auth('client')->id())->toBe($client->id)
        ->and($client->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue()
        ->and($user->id)->not->toBe($client->id);
});

it('clears client oauth intent when starting web user google login', function () {
    $tenant = createClientAuthTenant('intent-clear-tenant');

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-intent-1',
        'name' => 'Intent User',
        'email' => 'intent@example.com',
    ]));

    $response = $this->withSession(['client_auth_tenant_id' => $tenant->id])
        ->get(route('auth.social', ['social' => 'google']));

    $response->assertRedirect();

    expect(session()->has('client_auth_tenant_id'))->toBeFalse();
});

it('uses tenant profile data from tenantables instead of the client record', function () {
    $tenant = createClientAuthTenant();

    $client = Client::withoutGlobalScope('tenantable')->create([
        'name' => 'Global Name',
        'email' => 'profile@example.com',
        'tenant_id' => $tenant->id,
    ]);

    $client->tenants()->attach($tenant->id, [
        'active' => true,
        'meta' => [
            'name' => 'Tenant Controlled Name',
            'email' => 'tenant-profile@example.com',
            'phone' => '0500000000',
        ],
    ]);

    setCurrentTenant($tenant);

    $profile = $client->profileForTenant($tenant->id);

    expect($profile['name'])->toBe('Tenant Controlled Name')
        ->and($profile['email'])->toBe('tenant-profile@example.com')
        ->and($profile['phone'])->toBe('0500000000')
        ->and($client->name)->toBe('Global Name');
});
