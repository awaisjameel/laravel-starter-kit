<?php

declare(strict_types=1);

namespace App\Modules\Users\Handlers;

use App\Models\User;
use App\Modules\Users\Commands\UserCommandResult;
use App\Modules\Users\Commands\UserCommands;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Support\UserActionContext;
use Illuminate\Support\Facades\Event;

final readonly class UserCommandHandler
{
    public function __construct(
        private UserCommands $userCommands,
    ) {}

    public function create(CreateUserData $createUserData, UserActionContext $userActionContext): UserCommandResult
    {
        $userCommandResult = $this->userCommands->create($createUserData);

        Event::dispatch(new UserManagementEvent(
            action: 'create',
            actor: $userActionContext->actor,
            target: $userCommandResult->user,
            metadata: $userActionContext->metadata,
        ));

        return $userCommandResult;
    }

    public function update(User $user, UpdateUserData $updateUserData, UserActionContext $userActionContext): UserCommandResult
    {
        $userCommandResult = $this->userCommands->update($user, $updateUserData);

        Event::dispatch(new UserManagementEvent(
            action: 'update',
            actor: $userActionContext->actor,
            target: $userCommandResult->user,
            metadata: $userActionContext->metadata,
            changes: $userCommandResult->changes,
        ));

        return $userCommandResult;
    }

    public function delete(User $user, UserActionContext $userActionContext): void
    {
        Event::dispatch(new UserManagementEvent(
            action: 'delete',
            actor: $userActionContext->actor,
            target: $user,
            metadata: $userActionContext->metadata,
        ));

        $this->userCommands->delete($user);
    }
}
