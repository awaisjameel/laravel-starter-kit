<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Models\User;
use App\Modules\Shared\Enums\SortDirection;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Enums\UserSortBy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:100'],
            'sortBy' => ['nullable', 'string', new Enum(UserSortBy::class)],
            'sortDirection' => ['nullable', 'string', new Enum(SortDirection::class)],
        ];
    }

    public function pageNumber(): int
    {
        /** @var int|string|null $page */
        $page = $this->validated('page', 1);

        return (int) $page;
    }

    public function perPage(): int
    {
        /** @var int|string|null $perPage */
        $perPage = $this->validated('perPage', 10);

        return (int) $perPage;
    }

    public function search(): ?string
    {
        /** @var string|null $search */
        $search = $this->validated('search');

        if ($search === null) {
            return null;
        }

        $trimmed = mb_trim($search);

        return $trimmed !== '' ? $trimmed : null;
    }

    public function sortBy(): UserSortBy
    {
        /** @var UserSortBy|string|null $sortBy */
        $sortBy = $this->validated('sortBy');

        if ($sortBy instanceof UserSortBy) {
            return $sortBy;
        }

        if ($sortBy !== null) {
            return UserSortBy::from((string) $sortBy);
        }

        return UserSortBy::CreatedAt;
    }

    public function sortDirection(): SortDirection
    {
        /** @var SortDirection|string|null $sortDirection */
        $sortDirection = $this->validated('sortDirection');

        if ($sortDirection instanceof SortDirection) {
            return $sortDirection;
        }

        if ($sortDirection !== null) {
            return SortDirection::from((string) $sortDirection);
        }

        return SortDirection::Desc;
    }

    public function toDto(): UserIndexData
    {
        return new UserIndexData(
            page: $this->pageNumber(),
            perPage: $this->perPage(),
            search: $this->search(),
            sortBy: $this->sortBy(),
            sortDirection: $this->sortDirection(),
        );
    }
}
