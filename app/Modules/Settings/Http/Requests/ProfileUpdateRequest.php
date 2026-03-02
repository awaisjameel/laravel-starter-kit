<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Models\User;
use App\Modules\Settings\Data\ProfileUpdateData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
        ];
    }

    public function toDto(): ProfileUpdateData
    {
        /** @var array{name: string, email: string} $validated */
        $validated = $this->validated();

        return new ProfileUpdateData(
            name: $validated['name'],
            email: $validated['email'],
        );
    }
}
