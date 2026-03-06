<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\UsersIndexPageData;
use App\Modules\Users\Http\Requests\UserCreateRequest;
use App\Modules\Users\Http\Requests\UserDestroyRequest;
use App\Modules\Users\Http\Requests\UserIndexRequest;
use App\Modules\Users\Http\Requests\UserUpdateRequest;
use App\Modules\Users\Queries\UserQueries;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserQueries $userQueries,
        private readonly UserCommands $userCommands,
    ) {}

    public function index(UserIndexRequest $userIndexRequest): Response
    {
        $lengthAwarePaginator = $this->userQueries->paginate($userIndexRequest->toDto())->withQueryString();

        return Inertia::render(
            'modules/users/pages/Index',
            UsersIndexPageData::fromPaginator($lengthAwarePaginator)->toArray()
        );
    }

    public function store(UserCreateRequest $userCreateRequest): RedirectResponse
    {
        $this->userCommands->create(
            $userCreateRequest->toDto(),
            RequestActor::from($userCreateRequest),
            $userCreateRequest,
        );

        return redirect()->route('app.admin.users.index')
            ->with('message', 'User created successfully');
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): RedirectResponse
    {
        $this->userCommands->update(
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
        $this->userCommands->delete($user, RequestActor::from($userDestroyRequest), $userDestroyRequest);

        return redirect()->route('app.admin.users.index')
            ->with('message', 'User deleted successfully');
    }
}
