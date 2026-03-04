<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\PasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

final class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('modules/auth/pages/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(PasswordResetLinkRequest $passwordResetLinkRequest): RedirectResponse
    {
        $passwordResetLinkData = $passwordResetLinkRequest->toDto();

        Password::sendResetLink([
            'email' => $passwordResetLinkData->email,
        ]);

        return back()->with('status', __('A reset link will be sent if the account exists.'));
    }
}
