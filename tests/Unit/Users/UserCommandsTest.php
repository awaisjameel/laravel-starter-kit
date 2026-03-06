<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Events\UserManagementEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class UserCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_user_and_dispatches_management_event(): void
    {
        Event::fake([UserManagementEvent::class]);

        $actor = User::factory()->create(['role' => UserRole::Admin]);
        $request = Request::create('/app/admin/users', 'POST');

        $user = new UserCommands()->create(
            new CreateUserData(
                name: 'Created User',
                email: 'created-user@example.com',
                role: UserRole::User,
                password: 'Password123!@#',
            ),
            $actor,
            $request,
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'created-user@example.com',
            'role' => UserRole::User->value,
        ]);

        Event::assertDispatched(UserManagementEvent::class, static fn(UserManagementEvent $userManagementEvent): bool => $userManagementEvent->action === 'create'
            && $userManagementEvent->actor->is($actor)
            && $userManagementEvent->target?->is($user) === true
            && $userManagementEvent->request === $request
            && $userManagementEvent->changes === []);
    }

    public function test_update_persists_changes_and_dispatches_audited_management_event(): void
    {
        Event::fake([UserManagementEvent::class]);

        $actor = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create([
            'name' => 'Before Name',
            'email' => 'before@example.com',
            'role' => UserRole::User,
        ]);
        $request = Request::create('/app/admin/users/'.$user->id, 'PUT');

        $updatedUser = new UserCommands()->update(
            $user,
            new UpdateUserData(
                name: 'After Name',
                email: 'after@example.com',
                role: UserRole::Admin,
                password: 'Password456!@#',
            ),
            $actor,
            $request,
        );

        $this->assertDatabaseHas('users', [
            'id' => $updatedUser->id,
            'name' => 'After Name',
            'email' => 'after@example.com',
            'role' => UserRole::Admin->value,
        ]);

        Event::assertDispatched(UserManagementEvent::class, static fn(UserManagementEvent $userManagementEvent): bool => $userManagementEvent->action === 'update'
            && $userManagementEvent->actor->is($actor)
            && $userManagementEvent->target?->is($updatedUser) === true
            && $userManagementEvent->request === $request
            && $userManagementEvent->changes['name'] === ['before' => 'Before Name', 'after' => 'After Name']
            && $userManagementEvent->changes['email'] === ['before' => 'before@example.com', 'after' => 'after@example.com']
            && $userManagementEvent->changes['role'] === ['before' => 'user', 'after' => 'admin']
            && $userManagementEvent->changes['password'] === ['before' => '[REDACTED]', 'after' => '[REDACTED]']);
    }

    public function test_delete_removes_user_and_dispatches_management_event(): void
    {
        Event::fake([UserManagementEvent::class]);

        $actor = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create(['role' => UserRole::User]);
        $request = Request::create('/app/admin/users/'.$user->id, 'DELETE');

        new UserCommands()->delete($user, $actor, $request);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        Event::assertDispatched(UserManagementEvent::class, static fn(UserManagementEvent $userManagementEvent): bool => $userManagementEvent->action === 'delete'
            && $userManagementEvent->actor->is($actor)
            && $userManagementEvent->target instanceof User
            && $userManagementEvent->request === $request
            && $userManagementEvent->changes === []);
    }
}
