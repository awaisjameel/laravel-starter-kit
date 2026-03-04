<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Modules\Settings\Data\PasswordUpdateData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class PasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    public function toDto(): PasswordUpdateData
    {
        /** @var array{current_password: string, password: string} $validated */
        $validated = $this->validated();

        return new PasswordUpdateData(
            currentPassword: $validated['current_password'],
            password: $validated['password'],
            passwordConfirmation: $this->string('password_confirmation')->toString(),
        );
    }
}
