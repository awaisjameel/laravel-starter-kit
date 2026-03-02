<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Events\UserManagementEvent;
use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Data\UserIndexData;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Stringable;

/**
 * Service for user management operations.
 * Encapsulates business logic for creating, updating, and deleting users.
 */
final class UserService
{
    /**
     * Get paginated users with optional filtering and sorting.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateUsers(UserIndexData $userIndexData): LengthAwarePaginator
    {
        /** @var Builder<User> $query */
        $query = User::query();

        $this->applySearch($query, $userIndexData->search);

        return $query
            ->orderBy($userIndexData->sortBy, $userIndexData->sortDirection)
            ->paginate(
                perPage: $userIndexData->perPage,
                page: $userIndexData->page,
            )
            ->withQueryString();
    }

    /**
     * Create a new user.
     */
    public function createUser(CreateUserData $createUserData, User $actor, Request $request): User
    {
        $user = User::create([
            'name' => $createUserData->name,
            'email' => $createUserData->email,
            'role' => $createUserData->role,
            'password' => $createUserData->password,
        ]);

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
     */
    public function updateUser(User $user, UpdateUserData $updateUserData, User $actor, Request $request): User
    {
        $before = $user->only(['name', 'email', 'role']);

        $user->name = $updateUserData->name;
        $user->email = $updateUserData->email;
        $user->role = $updateUserData->role;

        if ($updateUserData->password !== null && $updateUserData->password !== '') {
            $user->password = $updateUserData->password;
        }

        $user->save();

        $changes = $this->computeChanges($before, $user, $updateUserData->password !== null && $updateUserData->password !== '');

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
            return (string) $value->value;
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => '',
            is_array($value) => $this->encodeAuditArray($value),
            is_object($value) => '[object '.$value::class.']',
            is_resource($value) => '[resource '.get_resource_type($value).']',
            default => '[unknown]',
        };
    }

    /**
     * @param  array<mixed>  $value
     */
    private function encodeAuditArray(array $value): string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '[unserializable array]';
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $searchTerm = '%'.$search.'%';

        $query->where(function (Builder $builder) use ($searchTerm): void {
            $builder
                ->where('name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->orWhere('role', 'like', $searchTerm);
        });
    }
}
