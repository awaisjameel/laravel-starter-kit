<?php

declare(strict_types=1);
use App\Modules\Shared\Realtime\Support\ChannelPatternResolver;

test('it resolves channel patterns with scalar parameters', function (): void {
    $resolved = ChannelPatternResolver::resolve('users.{userId}.notifications', [
        'userId' => 42,
    ]);

    expect($resolved)->toBe('users.42.notifications');
});
test('it throws when a parameter is missing', function (): void {
    expect(
        fn (): string => ChannelPatternResolver::resolve('users.{userId}'),
    )->toThrow(InvalidArgumentException::class, 'Missing channel parameter "userId".');
});
