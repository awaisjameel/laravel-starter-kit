<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserManagementEvent;
use App\Support\AuditLogger;

/**
 * Listener that handles audit logging for user management events.
 */
final class LogUserManagementAudit
{
    /**
     * Handle the event.
     */
    public function handle(UserManagementEvent $userManagementEvent): void
    {
        AuditLogger::logUserManagement(
            action: $userManagementEvent->action,
            actor: $userManagementEvent->actor,
            target: $userManagementEvent->target,
            request: $userManagementEvent->request,
            changes: $userManagementEvent->changes,
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            UserManagementEvent::class => 'handle',
        ];
    }
}
