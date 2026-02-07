<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\UserData;
use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\UserDestroyRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Models\User;
use App\Support\AuditLogger;
use BackedEnum;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function index(UserIndexRequest $userIndexRequest): Response
    {
        $lengthAwarePaginator = User::query()
            ->latest()
            ->paginate(
                perPage: $userIndexRequest->perPage(),
                page: $userIndexRequest->pageNumber(),
            )
            ->withQueryString()
            ->through(fn (User $user): UserData => $user->toData());

        return Inertia::render('users/Index', [
            'users' => $lengthAwarePaginator,
        ]);
    }

    public function store(UserCreateRequest $userCreateRequest): RedirectResponse
    {
        $actor = $userCreateRequest->user();
        if (! $actor instanceof User) {
            abort(403);
        }

        $userData = UserData::from($userCreateRequest->validated());
        $createdUser = User::create($userData->toArray());

        AuditLogger::logUserManagement(
            action: 'create',
            actor: $actor,
            target: $createdUser,
            request: $userCreateRequest,
        );

        return redirect()->route('users.index')
            ->with('message', 'User created successfully');
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): RedirectResponse
    {
        $actor = $userUpdateRequest->user();
        if (! $actor instanceof User) {
            abort(403);
        }

        $before = $user->only(['name', 'email', 'role']);

        $validated = $userUpdateRequest->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

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

        if (! empty($validated['password'])) {
            $changes['password'] = [
                'before' => '[REDACTED]',
                'after' => '[REDACTED]',
            ];
        }

        AuditLogger::logUserManagement(
            action: 'update',
            actor: $actor,
            target: $user,
            request: $userUpdateRequest,
            changes: $changes,
        );

        return redirect()->route('users.index')
            ->with('message', 'User updated successfully');
    }

    public function destroy(UserDestroyRequest $userDestroyRequest, User $user): RedirectResponse
    {
        $actor = $userDestroyRequest->user();
        if (! $actor instanceof User) {
            abort(403);
        }

        AuditLogger::logUserManagement(
            action: 'delete',
            actor: $actor,
            target: $user,
            request: $userDestroyRequest,
        );

        $user->delete();

        return redirect()->route('users.index')
            ->with('message', 'User deleted successfully');
    }

    private function auditValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}
