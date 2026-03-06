<?php

declare(strict_types=1);

namespace Tests\Unit\Realtime;

use App\Modules\Shared\Realtime\Support\ChannelPatternResolver;
use InvalidArgumentException;
use Tests\TestCase;

final class ChannelPatternResolverTest extends TestCase
{
    public function test_it_resolves_channel_patterns_with_scalar_parameters(): void
    {
        $resolved = ChannelPatternResolver::resolve('users.{userId}.notifications', [
            'userId' => 42,
        ]);

        $this->assertSame('users.42.notifications', $resolved);
    }

    public function test_it_throws_when_a_parameter_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing channel parameter "userId".');

        ChannelPatternResolver::resolve('users.{userId}');
    }
}
