<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user instanceof User ? $user->id : null),
            ],
            'role' => ['required', 'string', new Enum(UserRole::class)],
            'password' => ['nullable', 'string', Password::defaults()],
        ];
    }
}
