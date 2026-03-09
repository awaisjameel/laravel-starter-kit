<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $testResponse = $this->get('/auth/forgot-password');

        $testResponse
            ->assertStatus(200)
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->where('status', null)
            );
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/auth/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/auth/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification): true {
            $testResponse = $this->get('/auth/reset-password/'.$notification->token);

            $testResponse
                ->assertStatus(200)
                ->assertInertia(fn (Assert $assert): Assert => $assert
                    ->where('email', '')
                    ->where('token', $notification->token)
                );

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/auth/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user): true {
            $testResponse = $this->post('/auth/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $testResponse
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('auth.login.create', absolute: false));

            return true;
        });
    }
}
