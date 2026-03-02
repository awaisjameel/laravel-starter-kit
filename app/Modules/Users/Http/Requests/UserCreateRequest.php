<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

final class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', new Enum(UserRole::class)],
        ];
    }

    public function toDto(): CreateUserData
    {
        /** @var array{name: string, email: string, password: string, role: UserRole|string} $validated */
        $validated = $this->validated();

        $role = $validated['role'] instanceof UserRole ? $validated['role'] : UserRole::from((string) $validated['role']);

        return new CreateUserData(
            name: $validated['name'],
            email: $validated['email'],
            role: $role,
            password: $validated['password'],
        );
    }
}
