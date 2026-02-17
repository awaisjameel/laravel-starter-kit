<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\UserManagementEvent;
use App\Listeners\LogUserManagementAudit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            UserManagementEvent::class,
            LogUserManagementAudit::class,
        );
    }
}
