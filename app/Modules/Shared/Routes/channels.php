<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}.notifications', static fn (User $user, int $userId): bool => $user->id === $userId);
