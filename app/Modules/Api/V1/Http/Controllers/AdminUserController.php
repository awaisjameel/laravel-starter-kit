<?php

declare(strict_types=1);

namespace App\Modules\Api\V1\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Api\V1\Http\Resources\UserResource;
use App\Modules\Users\Handlers\UserCommandHandler;
use App\Modules\Users\Handlers\UserQueryHandler;
use App\Modules\Users\Http\Requests\UserCreateRequest;
use App\Modules\Users\Http\Requests\UserDestroyRequest;
use App\Modules\Users\Http\Requests\UserIndexRequest;
use App\Modules\Users\Http\Requests\UserUpdateRequest;
use App\Modules\Users\Support\UserActionContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserQueryHandler $userQueryHandler,
        private readonly UserCommandHandler $userCommandHandler,
    ) {}

    public function index(UserIndexRequest $userIndexRequest): AnonymousResourceCollection
    {
        $lengthAwarePaginator = $this->userQueryHandler->index($userIndexRequest->toDto())->withQueryString();

        return UserResource::collection($lengthAwarePaginator);
    }

    public function store(UserCreateRequest $userCreateRequest): JsonResponse
    {
        $userCommandResult = $this->userCommandHandler->create(
            $userCreateRequest->toDto(),
            UserActionContext::fromRequest($userCreateRequest),
        );

        return UserResource::make($userCommandResult->user)->response()->setStatusCode(201);
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): JsonResponse
    {
        $userCommandResult = $this->userCommandHandler->update(
            $user,
            $userUpdateRequest->toDto(),
            UserActionContext::fromRequest($userUpdateRequest),
        );

        return UserResource::make($userCommandResult->user)->response();
    }

    public function destroy(UserDestroyRequest $userDestroyRequest, User $user): JsonResponse
    {
        $this->userCommandHandler->delete($user, UserActionContext::fromRequest($userDestroyRequest));

        return response()->json([], 204);
    }
}
