<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UsersIndexPageData extends Data
{
    public function __construct(
        public UsersPaginationData $users,
    ) {}

    /**
     * @param  LengthAwarePaginator<int, User>  $lengthAwarePaginator
     */
    public static function fromPaginator(LengthAwarePaginator $lengthAwarePaginator): self
    {
        return new self(
            users: UsersPaginationData::fromPaginator($lengthAwarePaginator),
        );
    }
}
