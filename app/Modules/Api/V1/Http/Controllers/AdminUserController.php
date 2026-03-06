<?php

declare(strict_types=1);

namespace App\Modules\Api\V1\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Api\V1\Http\Resources\UserResource;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Users\Http\Requests\UserCreateRequest;
use App\Modules\Users\Http\Requests\UserDestroyRequest;
use App\Modules\Users\Http\Requests\UserIndexRequest;
use App\Modules\Users\Http\Requests\UserUpdateRequest;
use App\Modules\Users\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(UserIndexRequest $userIndexRequest): AnonymousResourceCollection
    {
        $lengthAwarePaginator = $this->userService->paginateUsers($userIndexRequest->toDto());

        return UserResource::collection($lengthAwarePaginator);
    }

    public function store(UserCreateRequest $userCreateRequest): JsonResponse
    {
        $user = $this->userService->createUser(
            $userCreateRequest->toDto(),
            RequestActor::from($userCreateRequest),
            $userCreateRequest,
        );

        return UserResource::make($user)->response()->setStatusCode(201);
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): JsonResponse
    {
        $updatedUser = $this->userService->updateUser(
            $user,
            $userUpdateRequest->toDto(),
            RequestActor::from($userUpdateRequest),
            $userUpdateRequest,
        );

        return UserResource::make($updatedUser)->response();
    }

    public function destroy(UserDestroyRequest $userDestroyRequest, User $user): JsonResponse
    {
        $this->userService->deleteUser($user, RequestActor::from($userDestroyRequest), $userDestroyRequest);

        return response()->json([], 204);
    }
}
