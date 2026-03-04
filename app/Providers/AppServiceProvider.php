<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Listeners\LogUserManagementAudit;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        RateLimiter::for('auth-sensitive', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->ip()));

        ResetPassword::createUrlUsing(function (mixed $user, string $token): string {
            if (! $user instanceof User) {
                return route('auth.password.reset', ['token' => $token]);
            }

            return route('auth.password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);
        });

        VerifyEmail::createUrlUsing(fn (User $user): string => URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1((string) $user->getEmailForVerification()),
            ],
        ));

        Event::listen(
            UserManagementEvent::class,
            LogUserManagementAudit::class,
        );
    }
}
