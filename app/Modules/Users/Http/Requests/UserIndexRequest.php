<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
}
