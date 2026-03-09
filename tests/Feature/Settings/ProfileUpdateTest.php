<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
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
    }

    public function test_profile_information_can_be_updated(): void
    {
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

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
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

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
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
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
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

        $this->assertNotNull($user->fresh());
    }
}
