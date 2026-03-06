<?php

declare(strict_types=1);

namespace App\Modules\Users\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final readonly class UserActionMetadata
{
    public function __construct(
        public ?string $ipAddress,
        public ?string $userAgent,
        public ?string $socketId,
        public CarbonImmutable $occurredAt,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $socketId = $request->header('X-Socket-ID');

        return new self(
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            socketId: is_string($socketId) && $socketId !== '' ? $socketId : null,
            occurredAt: CarbonImmutable::now(),
        );
    }
}
