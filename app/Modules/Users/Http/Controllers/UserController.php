<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Users\Data\UsersIndexPageData;
use App\Modules\Users\Http\Requests\UserCreateRequest;
use App\Modules\Users\Http\Requests\UserDestroyRequest;
use App\Modules\Users\Http\Requests\UserIndexRequest;
use App\Modules\Users\Http\Requests\UserUpdateRequest;
use App\Modules\Users\Services\UserService;
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
        $lengthAwarePaginator = $this->userService->paginateUsers($userIndexRequest->toDto())->withQueryString();

        return Inertia::render(
            'modules/users/pages/Index',
            UsersIndexPageData::fromPaginator($lengthAwarePaginator)->toArray()
        );
    }

    public function store(UserCreateRequest $userCreateRequest): RedirectResponse
    {
        $this->userService->createUser(
            $userCreateRequest->toDto(),
            RequestActor::from($userCreateRequest),
            $userCreateRequest,
        );

        return redirect()->route('app.admin.users.index')
            ->with('message', 'User created successfully');
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): RedirectResponse
    {
        $this->userService->updateUser(
            $user,
            $userUpdateRequest->toDto(),
            RequestActor::from($userUpdateRequest),
            $userUpdateRequest
        );

        return redirect()->route('app.admin.users.index')
            ->with('message', 'User updated successfully');
    }

    public function destroy(UserDestroyRequest $userDestroyRequest, User $user): RedirectResponse
    {
        $this->userService->deleteUser($user, RequestActor::from($userDestroyRequest), $userDestroyRequest);

        return redirect()->route('app.admin.users.index')
            ->with('message', 'User deleted successfully');
    }
}
