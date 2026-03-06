<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Tests\TestCase;

final class RequestActorTest extends TestCase
{
    public function test_it_returns_the_authenticated_application_user(): void
    {
        $user = User::factory()->make();
        $request = Request::create('/app/dashboard');
        $request->setUserResolver(static fn (): User => $user);

        $this->assertSame($user, RequestActor::from($request));
    }

    public function test_it_throws_when_no_authenticated_application_user_is_available(): void
    {
        $request = Request::create('/app/dashboard');
        $request->setUserResolver(static fn (): null => null);

        $this->expectException(AuthenticationException::class);

        RequestActor::from($request);
    }

    public function test_it_throws_when_the_authenticated_user_is_not_the_application_user_model(): void
    {
        $request = Request::create('/app/dashboard');
        $request->setUserResolver(static fn (): GenericUser => new GenericUser(['id' => 1, 'email' => 'generic@example.com']));

        $this->expectException(AuthenticationException::class);

        RequestActor::from($request);
    }
}
