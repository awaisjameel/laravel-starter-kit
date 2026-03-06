<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Contracts;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Http\Request;

interface RealtimeDispatcher
{
    public function dispatch(ShouldBroadcast $shouldBroadcast): void;

    public function dispatchToOthers(ShouldBroadcast $shouldBroadcast, ?Request $request = null): void;

    public function socketId(?Request $request = null): ?string;
}
