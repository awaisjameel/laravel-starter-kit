<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

final readonly class UserIndexData
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $search,
        public string $sortBy,
        public string $sortDirection,
    ) {}
}
