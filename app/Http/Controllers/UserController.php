<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\UserData;
use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\UserDestroyRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

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

        if ($actor === null) {
            return redirect()->route('login');
        }

        $userData = UserData::from($userCreateRequest->validated());

        $this->userService->createUser($userData, $actor, $userCreateRequest);

        return redirect()->route('users.index')
            ->with('message', 'User created successfully');
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): RedirectResponse
    {
        $actor = $userUpdateRequest->user();

        if ($actor === null) {
            return redirect()->route('login');
        }

        $userData = UserData::from($userUpdateRequest->validated());

        $this->userService->updateUser(
            $user,
            $userData,
            $actor,
            $userUpdateRequest
        );

        return redirect()->route('users.index')
            ->with('message', 'User updated successfully');
    }

    public function destroy(UserDestroyRequest $userDestroyRequest, User $user): RedirectResponse
    {
        $actor = $userDestroyRequest->user();

        if ($actor === null) {
            return redirect()->route('login');
        }

        $this->userService->deleteUser($user, $actor, $userDestroyRequest);

        return redirect()->route('users.index')
            ->with('message', 'User deleted successfully');
    }
}
