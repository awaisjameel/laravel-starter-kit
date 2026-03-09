<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Modules\Shared\Support\ModuleRegistry;
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
        $gateFiles = ModuleRegistry::gateFiles(base_path());

        foreach ($gateFiles as $gateFile) {
            if (! is_file($gateFile)) {
                continue;
            }

            require $gateFile;
        }
    }
}
