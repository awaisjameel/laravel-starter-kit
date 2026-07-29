<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('login screen can be rendered', function (): void {
    $testResponse = $this->get('/auth/login');

    $testResponse
        ->assertStatus(200)
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->where('canResetPassword', true)
            ->where('status', null)
        );
});
test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    $testResponse = $this->post('/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $testResponse->assertRedirect(route('app.dashboard', absolute: false));
});
test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    $this->post('/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
test('users can logout', function (): void {
    $user = User::factory()->create();

    $this->post('/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $testResponse = $this->post('/auth/logout');

    $this->assertGuest();
    $testResponse->assertRedirect('/');
});
