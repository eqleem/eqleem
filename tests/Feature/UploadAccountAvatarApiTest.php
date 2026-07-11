<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('guests cannot upload account avatar', function () {
    $this->postJson('/api/account/avatar', [])
        ->assertUnauthorized();
});

test('authenticated users can upload an account avatar', function () {
    Storage::fake('spaces');

    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'image' => null,
    ]);

    $file = UploadedFile::fake()->image('avatar.jpg', 400, 400);

    $this->actingAs($user)
        ->postJson('/api/account/avatar', [
            'file' => $file,
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'image'], 'message']);

    $user->refresh();

    expect($user->getRawOriginal('image'))->not->toBeNull()
        ->and($user->getRawOriginal('image'))->toContain('user-media/'.$user->uuid.'/avatar');

    Storage::disk('spaces')->assertExists($user->getRawOriginal('image'));
});

test('avatar upload validates the file', function () {
    Storage::fake('spaces');

    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->postJson('/api/account/avatar', [
            'file' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});
