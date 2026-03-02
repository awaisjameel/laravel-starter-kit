<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $emailVerificationRequest): RedirectResponse
    {
        $user = $emailVerificationRequest->user();

        if ($user === null) {
            return redirect()->route('auth.login.create');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('app.dashboard', absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            /** @var MustVerifyEmail $verifiedUser */
            $verifiedUser = $user;
            event(new Verified($verifiedUser));
        }

        return redirect()->intended(route('app.dashboard', absolute: false).'?verified=1');
    }
}
