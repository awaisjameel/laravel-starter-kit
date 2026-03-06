<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class BroadcastingAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('broadcasting.default', 'reverb');
        config()->set('broadcasting.connections.reverb.key', 'test-key');
        config()->set('broadcasting.connections.reverb.secret', 'test-secret');
        config()->set('broadcasting.connections.reverb.app_id', 'test-app');
        config()->set('broadcasting.connections.reverb.options.host', '127.0.0.1');
        config()->set('broadcasting.connections.reverb.options.port', 8080);
        config()->set('broadcasting.connections.reverb.options.scheme', 'http');
        config()->set('broadcasting.connections.reverb.options.useTLS', false);

        Broadcast::forgetDrivers();
        require base_path('routes/channels.php');
    }

    public function test_guests_cannot_authorize_private_broadcast_channels(): void
    {
        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'private-users.index',
            'socket_id' => '1234.5678',
        ])->assertForbidden();
    }

    public function test_non_admin_users_cannot_authorize_admin_private_channels(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-users.index',
            'socket_id' => '1234.5678',
        ])->assertForbidden();
    }

    public function test_admin_users_can_authorize_private_channels_via_web_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post('/broadcasting/auth', [
            'channel_name' => 'private-users.index',
            'socket_id' => '1234.5678',
        ])->assertOk()->assertSee('auth');
    }

    public function test_admin_users_can_authorize_presence_channels_via_web_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post('/broadcasting/auth', [
            'channel_name' => 'presence-users.index.presence',
            'socket_id' => '1234.5678',
        ])->assertOk()->assertSee('auth')->assertSee('channel_data');
    }

    public function test_admin_users_can_authorize_private_channels_via_sanctum(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Sanctum::actingAs($admin);

        $this->post('/api/broadcasting/auth', [
            'channel_name' => 'private-users.index',
            'socket_id' => '1234.5678',
        ])->assertOk()->assertSee('auth');
    }

    public function test_non_admin_users_cannot_authorize_private_channels_via_sanctum(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        Sanctum::actingAs($user);

        $this->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-users.index',
            'socket_id' => '1234.5678',
        ])->assertForbidden();
    }
}
