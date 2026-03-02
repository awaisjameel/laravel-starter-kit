<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Models\User;
use App\Modules\Users\Data\UserIndexData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'sortBy' => ['nullable', 'string', Rule::in(['name', 'email', 'role', 'created_at'])],
            'sortDirection' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
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

    public function sortBy(): string
    {
        /** @var string|null $sortBy */
        $sortBy = $this->validated('sortBy');

        return $sortBy ?? 'created_at';
    }

    public function sortDirection(): string
    {
        /** @var string|null $sortDirection */
        $sortDirection = $this->validated('sortDirection');

        return $sortDirection ?? 'desc';
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
