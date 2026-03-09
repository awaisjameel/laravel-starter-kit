<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UserViewData extends Data
{
    public int $id;

    public CarbonImmutable $created_at;

    public CarbonImmutable $updated_at;

    public function __construct(
        #[Rule(['required', 'string'])]
        public string $name,

        #[Rule(['required', 'string', 'email', 'max:255'])]
        public string $email,

        #[Rule(['required', 'string', new Enum(UserRole::class)])]
        #[WithCast(EnumCast::class, type: UserRole::class)]
        public UserRole $role,

        #[Rule(['nullable', 'date'])]
        public ?CarbonImmutable $email_verified_at = null,
    ) {}

    public static function fromModel(User $user): self
    {
        $emailVerifiedAt = $user->email_verified_at;
        $emailVerifiedAt = $emailVerifiedAt instanceof Carbon ? $emailVerifiedAt->toImmutable() : null;

        $data = new self(
            name: $user->name,
            email: $user->email,
            role: $user->role,
            email_verified_at: $emailVerifiedAt,
        );

        $data->id = $user->id;
        $data->updated_at = CarbonImmutable::parse($user->updated_at);
        $data->created_at = CarbonImmutable::parse($user->created_at);

        return $data;
    }
}
