<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('guests cannot update account password', function () {
    $this->putJson('/api/account/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertUnauthorized();
});

test('authenticated users can update their password', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->putJson('/api/account/password', [
            'current_password' => 'password',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['message']);

    expect(Hash::check('secret123', $user->fresh()->password))->toBeTrue();
});

test('password update requires the current password', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->putJson('/api/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);
});

test('password update requires confirmation match', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user)
        ->putJson('/api/account/password', [
            'current_password' => 'password',
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});
