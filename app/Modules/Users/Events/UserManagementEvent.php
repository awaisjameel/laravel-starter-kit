<?php

declare(strict_types=1);

namespace App\Modules\Users\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when user management actions occur.
 * Listeners can handle side effects like audit logging, notifications, etc.
 */
final readonly class UserManagementEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public string $action,
        public User $actor,
        public ?User $target,
        public Request $request,
        public array $changes = [],
    ) {}
}
