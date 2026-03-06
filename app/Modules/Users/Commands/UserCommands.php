<?php

declare(strict_types=1);

namespace App\Modules\Users\Commands;

use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Events\UserManagementEvent;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Stringable;

final class UserCommands
{
    public function create(CreateUserData $createUserData, User $actor, Request $request): User
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

    public function update(User $user, UpdateUserData $updateUserData, User $actor, Request $request): User
    {
        $before = $user->only(['name', 'email', 'role']);

        $user->name = $updateUserData->name;
        $user->email = $updateUserData->email;
        $user->role = $updateUserData->role;

        $passwordChanged = $updateUserData->password !== null && $updateUserData->password !== '';

        if ($passwordChanged) {
            $user->password = $updateUserData->password;
        }

        $user->save();

        Event::dispatch(new UserManagementEvent(
            action: 'update',
            actor: $actor,
            target: $user,
            request: $request,
            changes: $this->computeChanges($before, $user, $passwordChanged),
        ));

        return $user;
    }

    public function delete(User $user, User $actor, Request $request): void
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
}
