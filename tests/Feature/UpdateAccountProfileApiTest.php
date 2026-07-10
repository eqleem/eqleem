<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('guests cannot update account profile', function () {
    $this->putJson('/api/account/profile', [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ])->assertUnauthorized();
});

test('authenticated users can update their profile', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($user)
        ->putJson('/api/account/profile', [
            'name' => 'أحمد الأحمدي',
            'email' => 'contact@ahmad.tech',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'أحمد الأحمدي')
        ->assertJsonPath('data.email', 'contact@ahmad.tech')
        ->assertJsonStructure(['message']);

    expect($user->fresh())
        ->name->toBe('أحمد الأحمدي')
        ->email->toBe('contact@ahmad.tech');
});

test('profile update validates required fields', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->putJson('/api/account/profile', [
            'name' => '',
            'email' => 'not-an-email',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

test('profile update rejects emails already taken by another user', function () {
    User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'taken@example.com',
    ]);

    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'mine@example.com',
    ]);

    $this->actingAs($user)
        ->putJson('/api/account/profile', [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('profile update allows keeping the same email', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'same@example.com',
    ]);

    $this->actingAs($user)
        ->putJson('/api/account/profile', [
            'name' => 'Updated Name',
            'email' => 'same@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.email', 'same@example.com');
});
