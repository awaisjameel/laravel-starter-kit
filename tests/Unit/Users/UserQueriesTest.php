<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Enums\SortDirection;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Enums\UserSortBy;
use App\Modules\Users\Queries\UserQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_applies_search_sort_and_pagination(): void
    {
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

        $this->assertSame(1, $lengthAwarePaginator->currentPage());
        $this->assertSame(1, $lengthAwarePaginator->perPage());
        $this->assertSame(2, $lengthAwarePaginator->total());
        $this->assertCount(1, $lengthAwarePaginator->items());
        $this->assertSame('zulu-query@example.com', $lengthAwarePaginator->items()[0]->email);
    }
}
