<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Support;

use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Http\Request;

final readonly class LaravelRealtimeDispatcher implements RealtimeDispatcher
{
    public function __construct(
        private BroadcastManager $broadcastManager,
    ) {}

    public function dispatch(ShouldBroadcast $shouldBroadcast): void
    {
        $pendingBroadcast = $this->broadcastManager->event($shouldBroadcast);

        unset($pendingBroadcast);
    }

    public function dispatchToOthers(ShouldBroadcast $shouldBroadcast, ?Request $request = null): void
    {
        $pendingBroadcast = $this->broadcastManager->event($shouldBroadcast);

        if ($this->socketId($request) !== null) {
            $pendingBroadcast->toOthers();
        }

        unset($pendingBroadcast);
    }

    public function socketId(?Request $request = null): ?string
    {
        return $this->broadcastManager->socket($request);
    }
}
