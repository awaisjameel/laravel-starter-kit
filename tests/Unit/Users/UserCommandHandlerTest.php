<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Mutations\MutationMetadata;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Handlers\UserCommandHandler;
use App\Modules\Users\Support\UserActionContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class UserCommandHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_dispatches_management_event_with_context_metadata(): void
    {
        Event::fake([UserManagementEvent::class]);

        $actor = User::factory()->create(['role' => UserRole::Admin]);
        $userCommandResult = app(UserCommandHandler::class)->create(
            new CreateUserData(
                name: 'Created User',
                email: 'created-handler@example.com',
                role: UserRole::User,
                password: 'Password123!@#',
            ),
            $this->userActionContext($actor),
        );

        Event::assertDispatched(UserManagementEvent::class, static fn (UserManagementEvent $userManagementEvent): bool => $userManagementEvent->context->action === 'create'
            && $userManagementEvent->context->actor->is($actor)
            && $userManagementEvent->context->target?->is($userCommandResult->user) === true
            && $userManagementEvent->context->ipAddress() === '127.0.0.1'
            && $userManagementEvent->context->userAgent() === 'PHPUnit'
            && $userManagementEvent->context->socketId() === '1234.5678'
            && $userManagementEvent->context->changes === []);
    }

    public function test_update_dispatches_management_event_with_audited_changes(): void
    {
        Event::fake([UserManagementEvent::class]);

        $actor = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create([
            'name' => 'Before Name',
            'email' => 'before@example.com',
            'role' => UserRole::User,
        ]);

        app(UserCommandHandler::class)->update(
            $target,
            new UpdateUserData(
                name: 'After Name',
                email: 'after@example.com',
                role: UserRole::Admin,
                password: 'Password456!@#',
            ),
            $this->userActionContext($actor),
        );

        Event::assertDispatched(UserManagementEvent::class, static fn (UserManagementEvent $userManagementEvent): bool => $userManagementEvent->context->action === 'update'
            && $userManagementEvent->context->actor->is($actor)
            && $userManagementEvent->context->target?->is($target) === true
            && $userManagementEvent->context->changes['name'] === ['before' => 'Before Name', 'after' => 'After Name']
            && $userManagementEvent->context->changes['email'] === ['before' => 'before@example.com', 'after' => 'after@example.com']
            && $userManagementEvent->context->changes['role'] === ['before' => 'user', 'after' => 'admin']
            && $userManagementEvent->context->changes['password'] === ['before' => '[REDACTED]', 'after' => '[REDACTED]']);
    }

    public function test_delete_dispatches_management_event_before_removing_user(): void
    {
        Event::fake([UserManagementEvent::class]);

        $actor = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User]);

        app(UserCommandHandler::class)->delete($target, $this->userActionContext($actor));

        Event::assertDispatched(UserManagementEvent::class, static fn (UserManagementEvent $userManagementEvent): bool => $userManagementEvent->context->action === 'delete'
            && $userManagementEvent->context->actor->is($actor)
            && $userManagementEvent->context->target?->id === $target->id
            && $userManagementEvent->context->socketId() === '1234.5678'
            && $userManagementEvent->context->changes === []);
    }

    private function userActionContext(User $user): UserActionContext
    {
        return new UserActionContext(
            actor: $user,
            metadata: new MutationMetadata(
                ipAddress: '127.0.0.1',
                userAgent: 'PHPUnit',
                socketId: '1234.5678',
                occurredAt: CarbonImmutable::parse('2026-03-07T10:15:00+00:00'),
            ),
        );
    }
}
