<?php

declare(strict_types=1);

namespace App\Modules\Users\Support;

use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use Illuminate\Http\Request;

final readonly class UserActionContext
{
    public function __construct(
        public User $actor,
        public UserActionMetadata $metadata,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            actor: RequestActor::from($request),
            metadata: UserActionMetadata::fromRequest($request),
        );
    }
}
