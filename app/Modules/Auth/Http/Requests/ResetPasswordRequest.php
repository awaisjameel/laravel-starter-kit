<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Data\ResetPasswordData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    public function toDto(): ResetPasswordData
    {
        /** @var array{token: string, email: string, password: string} $validated */
        $validated = $this->validated();

        return new ResetPasswordData(
            token: $validated['token'],
            email: $validated['email'],
            password: $validated['password'],
            passwordConfirmation: $this->string('password_confirmation')->toString(),
        );
    }
}
