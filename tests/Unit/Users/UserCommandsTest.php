<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Commands\UserCommandResult;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_user_and_returns_command_result(): void
    {
        $userCommandResult = new UserCommands()->create(
            new CreateUserData(
                name: 'Created User',
                email: 'created-user@example.com',
                role: UserRole::User,
                password: 'Password123!@#',
            ),
        );

        $this->assertInstanceOf(UserCommandResult::class, $userCommandResult);
        $this->assertDatabaseHas('users', [
            'id' => $userCommandResult->user->id,
            'email' => 'created-user@example.com',
            'role' => UserRole::User->value,
        ]);
        $this->assertSame([], $userCommandResult->changes);
    }

    public function test_update_persists_changes_and_returns_audited_command_result(): void
    {
        $user = User::factory()->create([
            'name' => 'Before Name',
            'email' => 'before@example.com',
            'role' => UserRole::User,
        ]);

        $userCommandResult = new UserCommands()->update(
            $user,
            new UpdateUserData(
                name: 'After Name',
                email: 'after@example.com',
                role: UserRole::Admin,
                password: 'Password456!@#',
            ),
        );

        $this->assertDatabaseHas('users', [
            'id' => $userCommandResult->user->id,
            'name' => 'After Name',
            'email' => 'after@example.com',
            'role' => UserRole::Admin->value,
        ]);
        $this->assertSame(['before' => 'Before Name', 'after' => 'After Name'], $userCommandResult->changes['name']);
        $this->assertSame(['before' => 'before@example.com', 'after' => 'after@example.com'], $userCommandResult->changes['email']);
        $this->assertSame(['before' => 'user', 'after' => 'admin'], $userCommandResult->changes['role']);
        $this->assertSame(['before' => '[REDACTED]', 'after' => '[REDACTED]'], $userCommandResult->changes['password']);
    }

    public function test_delete_removes_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        new UserCommands()->delete($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
