<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Handlers\UserCommandHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('create dispatches management event with context metadata', function (): void {
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
        && $userManagementEvent->context->userAgent() === 'Pest'
        && $userManagementEvent->context->socketId() === '1234.5678'
        && $userManagementEvent->context->changes === []);
});
test('update dispatches management event with audited changes', function (): void {
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

    Event::assertDispatched(UserManagementEvent::class, static function (UserManagementEvent $userManagementEvent) use ($actor, $target): bool {
        expect($userManagementEvent->context->changes)->toBe([
            'name' => ['before' => 'Before Name', 'after' => 'After Name'],
            'email' => ['before' => 'before@example.com', 'after' => 'after@example.com'],
            'role' => ['before' => 'user', 'after' => 'admin'],
            'password' => ['before' => '[REDACTED]', 'after' => '[REDACTED]'],
        ]);

        return $userManagementEvent->context->action === 'update'
            && $userManagementEvent->context->actor->is($actor)
            && $userManagementEvent->context->target?->is($target) === true;
    });
});
test('delete dispatches management event after removing user', function (): void {
    Event::fake([UserManagementEvent::class]);

    $actor = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['role' => UserRole::User]);

    app(UserCommandHandler::class)->delete($target, $this->userActionContext($actor));

    Event::assertDispatched(UserManagementEvent::class, static fn (UserManagementEvent $userManagementEvent): bool => $userManagementEvent->context->action === 'delete'
        && $userManagementEvent->context->actor->is($actor)
        && $userManagementEvent->context->target?->id === $target->id
        && $userManagementEvent->context->socketId() === '1234.5678'
        && $userManagementEvent->context->changes === []);

    expect($target->exists)->toBeFalse();
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('failed deletion does not announce a successful mutation', function (): void {
    Event::fake([UserManagementEvent::class]);
    $actor = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create();
    User::deleting(static function (): never {
        throw new RuntimeException('Deletion failed');
    });

    expect(fn () => app(UserCommandHandler::class)->delete($target, $this->userActionContext($actor)))
        ->toThrow(RuntimeException::class, 'Deletion failed');

    Event::assertNotDispatched(UserManagementEvent::class);
    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

test('rolled back mutations do not dispatch side effects', function (): void {
    Event::fake([UserManagementEvent::class]);
    $actor = User::factory()->create(['role' => UserRole::Admin]);

    DB::beginTransaction();
    app(UserCommandHandler::class)->create(
        new CreateUserData('Rolled Back', 'rollback@example.com', UserRole::User, 'Password123!@#'),
        $this->userActionContext($actor),
    );
    Event::assertNotDispatched(UserManagementEvent::class);
    DB::rollBack();

    Event::assertNotDispatched(UserManagementEvent::class);
    $this->assertDatabaseMissing('users', ['email' => 'rollback@example.com']);
});
