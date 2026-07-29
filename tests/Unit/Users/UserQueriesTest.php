<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Enums\SortDirection;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Enums\UserSortBy;
use App\Modules\Users\Queries\UserQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('paginate applies search sort and pagination', function (): void {
    User::factory()->create([
        'name' => 'Alpha Query',
        'email' => 'alpha-query@example.com',
        'role' => UserRole::User,
    ]);
    User::factory()->create([
        'name' => 'Zulu Query',
        'email' => 'zulu-query@example.com',
        'role' => UserRole::Admin,
    ]);
    User::factory()->create([
        'name' => 'Ignored Person',
        'email' => 'ignored@example.com',
        'role' => UserRole::User,
    ]);

    $lengthAwarePaginator = new UserQueries()->paginate(new UserIndexData(
        page: 1,
        perPage: 1,
        search: 'Query',
        sortBy: UserSortBy::Name,
        sortDirection: SortDirection::Desc,
    ));

    expect($lengthAwarePaginator->currentPage())->toBe(1);
    expect($lengthAwarePaginator->perPage())->toBe(1);
    expect($lengthAwarePaginator->total())->toBe(2);
    expect($lengthAwarePaginator->items())->toHaveCount(1);
    expect($lengthAwarePaginator->getCollection()->sole()->email)->toBe('zulu-query@example.com');
});
