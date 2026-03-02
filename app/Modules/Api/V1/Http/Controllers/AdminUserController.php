<?php

declare(strict_types=1);

namespace App\Modules\Api\V1\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Api\V1\Http\Resources\UserResource;
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
        $actor = $userCreateRequest->user();

        if ($actor === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $this->userService->createUser($userCreateRequest->toDto(), $actor, $userCreateRequest);

        return UserResource::make($user)->response()->setStatusCode(201);
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): JsonResponse
    {
        $actor = $userUpdateRequest->user();

        if ($actor === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $updatedUser = $this->userService->updateUser($user, $userUpdateRequest->toDto(), $actor, $userUpdateRequest);

        return UserResource::make($updatedUser)->response();
    }

    public function destroy(UserDestroyRequest $userDestroyRequest, User $user): JsonResponse
    {
        $actor = $userDestroyRequest->user();

        if ($actor === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->userService->deleteUser($user, $actor, $userDestroyRequest);

        return response()->json([], 204);
    }
}
