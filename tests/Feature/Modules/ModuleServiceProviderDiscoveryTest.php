<?php

declare(strict_types=1);

namespace Tests\Feature\Modules;

use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use App\Modules\Shared\Realtime\Support\LaravelRealtimeDispatcher;
use Tests\TestCase;

final class ModuleServiceProviderDiscoveryTest extends TestCase
{
    public function test_shared_module_service_provider_is_auto_registered(): void
    {
        $this->assertTrue(app()->providerIsLoaded(ModuleServiceProvider::class));
        $this->assertInstanceOf(LaravelRealtimeDispatcher::class, app(RealtimeDispatcher::class));
    }
}
