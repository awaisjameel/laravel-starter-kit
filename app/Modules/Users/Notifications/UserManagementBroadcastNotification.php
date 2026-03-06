<?php

declare(strict_types=1);

namespace App\Modules\Users\Notifications;

use App\Models\User;
use App\Modules\Users\Data\UserManagementNotificationData;
use App\Modules\Users\Enums\UsersRealtimeAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

final class UserManagementBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UsersRealtimeAction $usersRealtimeAction,
        private readonly User $actor,
        private readonly ?User $target,
    ) {
        $this->onQueue('realtime');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            UserManagementNotificationData::from([
                'title' => 'User management updated',
                'description' => $this->description(),
                'action' => $this->usersRealtimeAction,
                'actorUserId' => $this->actor->id,
                'actorName' => $this->actor->name,
                'targetUserId' => $this->target?->id,
            ])->toArray()
        );
    }

    private function description(): string
    {
        $targetLabel = $this->target instanceof User ? $this->target->name : 'a user';

        return match ($this->usersRealtimeAction) {
            UsersRealtimeAction::Create => sprintf('%s created %s.', $this->actor->name, $targetLabel),
            UsersRealtimeAction::Update => sprintf('%s updated %s.', $this->actor->name, $targetLabel),
            UsersRealtimeAction::Delete => sprintf('%s deleted %s.', $this->actor->name, $targetLabel),
        };
    }
}
