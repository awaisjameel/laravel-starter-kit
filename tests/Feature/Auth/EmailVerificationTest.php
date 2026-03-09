<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $testResponse = $this->actingAs($user)->get('/auth/verify-email');

        $testResponse
            ->assertStatus(200)
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->where('status', null)
            );
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1((string) $user->email)]
        );

        $testResponse = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertTrue($freshUser->hasVerifiedEmail());
        $testResponse->assertRedirect(route('app.dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertFalse($freshUser->hasVerifiedEmail());
    }
}
