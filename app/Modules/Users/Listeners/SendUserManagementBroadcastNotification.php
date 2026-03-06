<?php

declare(strict_types=1);

namespace App\Modules\Users\Listeners;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Enums\UsersRealtimeAction;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Notifications\UserManagementBroadcastNotification;
use Illuminate\Support\Facades\Notification;

final class SendUserManagementBroadcastNotification
{
    public function handle(UserManagementEvent $userManagementEvent): void
    {
        $admins = User::query()
            ->where('role', UserRole::Admin)
            ->whereKeyNot($userManagementEvent->actor->id)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send(
            $admins,
            new UserManagementBroadcastNotification(
                usersRealtimeAction: UsersRealtimeAction::from($userManagementEvent->action),
                actor: $userManagementEvent->actor,
                target: $userManagementEvent->target,
            ),
        );
    }
}
