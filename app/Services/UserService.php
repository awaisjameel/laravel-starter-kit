<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\UserData;
use App\Events\UserManagementEvent;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

/**
 * Service for user management operations.
 * Encapsulates business logic for creating, updating, and deleting users.
 */
final class UserService
{
    /**
     * Create a new user.
     */
    public function createUser(UserData $userData, User $actor, Request $request): User
    {
        $user = User::create($userData->toArray());

        Event::dispatch(new UserManagementEvent(
            action: 'create',
            actor: $actor,
            target: $user,
            request: $request,
        ));

        return $user;
    }

    /**
     * Update an existing user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data, User $actor, Request $request): User
    {
        $before = $user->only(['name', 'email', 'role']);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        $changes = $this->computeChanges($before, $user, ! empty($data['password']));

        Event::dispatch(new UserManagementEvent(
            action: 'update',
            actor: $actor,
            target: $user,
            request: $request,
            changes: $changes,
        ));

        return $user;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user, User $actor, Request $request): void
    {
        Event::dispatch(new UserManagementEvent(
            action: 'delete',
            actor: $actor,
            target: $user,
            request: $request,
        ));

        $user->delete();
    }

    /**
     * Compute the changes between before and after states for audit logging.
     *
     * @param  array<string, mixed>  $before
     * @return array<string, array<string, string>>
     */
    private function computeChanges(array $before, User $user, bool $passwordChanged): array
    {
        $changes = [];

        foreach (['name', 'email', 'role'] as $key) {
            $beforeValue = $this->auditValue($before[$key]);
            $afterValue = $this->auditValue($user->{$key});

            if ($beforeValue !== $afterValue) {
                $changes[$key] = [
                    'before' => $beforeValue,
                    'after' => $afterValue,
                ];
            }
        }

        if ($passwordChanged) {
            $changes['password'] = [
                'before' => '[REDACTED]',
                'after' => '[REDACTED]',
            ];
        }

        return $changes;
    }

    /**
     * Convert a value to its audit-friendly representation.
     */
    private function auditValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return (string) $value;
    }
}
