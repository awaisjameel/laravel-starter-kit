<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use App\Modules\Shared\Realtime\Data\PresenceMemberData;
use App\Modules\Shared\Realtime\Support\LaravelRealtimeDispatcher;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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
        $this->app->singleton(RealtimeDispatcher::class, LaravelRealtimeDispatcher::class);
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

        Broadcast::resolved(static function (BroadcastManager $broadcastManager): void {
            $broadcaster = $broadcastManager->connection();

            if (! $broadcaster instanceof Broadcaster) {
                return;
            }

            $broadcaster->resolveAuthenticatedUserUsing(static function (Request $request): ?array {
                $user = $request->user();

                if (! $user instanceof User) {
                    return null;
                }

                return PresenceMemberData::fromUser($user)->toArray();
            });
        });
    }
}
