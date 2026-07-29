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
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing channel parameter "userId".');

    ChannelPatternResolver::resolve('users.{userId}');
});
