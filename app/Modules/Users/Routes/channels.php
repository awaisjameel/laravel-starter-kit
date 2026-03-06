<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Shared\Realtime\Data\PresenceMemberData;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('users.index', static fn (User $user): bool => Gate::forUser($user)->allows('manage-users'));

Broadcast::channel('users.index.presence', static function (User $user): array|bool {
    if (! Gate::forUser($user)->allows('manage-users')) {
        return false;
    }

    return PresenceMemberData::fromUser($user)->toArray();
});

Broadcast::channel('users.{userId}', static function (User $user, int $userId): bool {
    if (Gate::forUser($user)->allows('manage-users')) {
        return true;
    }

    return $user->id === $userId;
});
