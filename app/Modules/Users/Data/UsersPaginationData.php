<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Models\User;
use App\Modules\Shared\Data\UserViewData;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;

#[TypeScript]
final class UsersPaginationData extends Data
{
    /**
     * @param  array<int, UserViewData>  $data
     */
    public function __construct(
        #[DataCollectionOf(UserViewData::class)]
        #[TypeScriptType('array<\App\Modules\Shared\Data\UserViewData>')]
        public array $data,
        public int $per_page,
        public int $current_page,
        public ?int $from,
        public ?int $to,
        public int $last_page,
        public int $total
    ) {}

    /**
     * @param  LengthAwarePaginator<int, User>  $lengthAwarePaginator
     */
    public static function fromPaginator(LengthAwarePaginator $lengthAwarePaginator): self
    {
        /** @var list<UserViewData> $data */
        $data = array_values(array_map(
            static fn (User $user): UserViewData => $user->toViewData(),
            $lengthAwarePaginator->items(),
        ));

        return new self(
            data: $data,
            per_page: $lengthAwarePaginator->perPage(),
            current_page: $lengthAwarePaginator->currentPage(),
            from: $lengthAwarePaginator->firstItem(),
            to: $lengthAwarePaginator->lastItem(),
            last_page: $lengthAwarePaginator->lastPage(),
            total: $lengthAwarePaginator->total(),
        );
    }
}
