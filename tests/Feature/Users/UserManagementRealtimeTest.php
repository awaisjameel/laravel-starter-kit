<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Events\Broadcast\UserChanged;
use App\Modules\Users\Events\Broadcast\UsersListChanged;
use App\Modules\Users\Notifications\UserManagementBroadcastNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class UserManagementRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_user_dispatches_realtime_events_and_notifications(): void
    {
        Event::fake([UsersListChanged::class, UserChanged::class]);
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $otherAdmin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->withHeader('X-Socket-ID', '1234.5678')
            ->post('/app/admin/users', [
                'name' => 'Realtime User',
                'email' => 'realtime-user@example.com',
                'password' => 'Password123!@#',
                'role' => UserRole::User->value,
            ])
            ->assertRedirect('/app/admin/users');

        Event::assertDispatched(UsersListChanged::class, static function (UsersListChanged $usersListChanged) use ($admin): bool {
            $payload = $usersListChanged->broadcastWith();

            return $usersListChanged->broadcastAs() === 'users.list.changed'
                && $payload['action'] === 'create'
                && $payload['actorUserId'] === $admin->id;
        });

        Event::assertDispatched(UserChanged::class, static function (UserChanged $userChanged): bool {
            $payload = $userChanged->broadcastWith();

            return $userChanged->broadcastAs() === 'users.user.changed'
                && $payload['action'] === 'create'
                && is_array($payload['user'])
                && $payload['user']['email'] === 'realtime-user@example.com';
        });

        Notification::assertSentTo($otherAdmin, UserManagementBroadcastNotification::class);
    }

    public function test_updating_a_user_dispatches_realtime_events(): void
    {
        Event::fake([UsersListChanged::class, UserChanged::class]);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($admin)
            ->withHeader('X-Socket-ID', '1234.5678')
            ->put('/app/admin/users/'.$target->id, [
                'name' => 'Realtime Updated',
                'email' => 'realtime-updated@example.com',
                'password' => '',
                'role' => UserRole::Admin->value,
            ])
            ->assertRedirect('/app/admin/users');

        Event::assertDispatched(UsersListChanged::class, static fn (UsersListChanged $usersListChanged): bool => $usersListChanged->broadcastWith()['action'] === 'update');
        Event::assertDispatched(UserChanged::class, static fn (UserChanged $userChanged): bool => $userChanged->broadcastWith()['action'] === 'update');
    }

    public function test_deleting_a_user_dispatches_realtime_events(): void
    {
        Event::fake([UsersListChanged::class, UserChanged::class]);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($admin)
            ->withHeader('X-Socket-ID', '1234.5678')
            ->delete('/app/admin/users/'.$target->id)
            ->assertRedirect('/app/admin/users');

        Event::assertDispatched(UsersListChanged::class, static fn (UsersListChanged $usersListChanged): bool => $usersListChanged->broadcastWith()['action'] === 'delete');
        Event::assertDispatched(UserChanged::class, static fn (UserChanged $userChanged): bool => $userChanged->broadcastWith()['action'] === 'delete');
    }
}
