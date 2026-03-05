<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Modules\Shared\Support\ModuleGateDiscovery;
use App\Modules\Users\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $gateFiles = ModuleGateDiscovery::discover(
            basePath: base_path(),
            priorityModules: ['Users'],
        );

        foreach ($gateFiles as $gateFile) {
            require $gateFile;
        }
    }
}
