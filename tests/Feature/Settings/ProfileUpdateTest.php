<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->get('/app/settings/profile');

    $testResponse
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('mustVerifyEmail', true)
            ->where('status', null)
        );
});
test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->patch('/app/settings/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});
test('email verification status is unchanged when the email address is unchanged', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->patch('/app/settings/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/settings/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});
test('user can delete their account', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->delete('/app/settings/profile', [
            'password' => 'password',
        ]);

    $testResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});
test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create();

    $testResponse = $this
        ->actingAs($user)
        ->from('/app/settings/profile')
        ->delete('/app/settings/profile', [
            'password' => 'wrong-password',
        ]);

    $testResponse
        ->assertSessionHasErrors('password')
        ->assertRedirect('/app/settings/profile');

    expect($user->fresh())->not->toBeNull();
});
