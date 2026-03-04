<?php

declare(strict_types=1);

namespace App\Modules\Users\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class UserManagementAuditLogger
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public static function log(
        string $action,
        User $actor,
        ?User $target,
        Request $request,
        array $changes = []
    ): void {
        Log::channel('audit')->info('audit.user_management', [
            'action' => $action,
            'actor_id' => $actor->id,
            'actor_email' => $actor->email,
            'target_id' => $target?->id,
            'target_email' => $target?->email,
            'target_role' => $target?->role?->value,
            'changes' => $changes,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now()->toISOString(),
        ]);
    }
}
