<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class NewPasswordController extends Controller
{
    /**
     * Show the password reset page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('modules/auth/pages/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(ResetPasswordRequest $resetPasswordRequest): RedirectResponse
    {
        $resetPasswordData = $resetPasswordRequest->toDto();

        $status = Password::reset(
            [
                'email' => $resetPasswordData->email,
                'password' => $resetPasswordData->password,
                'password_confirmation' => $resetPasswordData->passwordConfirmation,
                'token' => $resetPasswordData->token,
            ],
            function ($user) use ($resetPasswordData): void {
                $user->forceFill([
                    'password' => Hash::make($resetPasswordData->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
        /** @var string $status */
        if ($status === Password::PasswordReset) {
            return to_route('auth.login.create')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__((string) $status)],
        ]);
    }
}
