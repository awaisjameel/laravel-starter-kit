<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('modules/settings/pages/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $passwordUpdateRequest): RedirectResponse
    {
        $user = $passwordUpdateRequest->user();

        if ($user === null) {
            return redirect()->route('auth.login.create');
        }

        $validated = $passwordUpdateRequest->validated();

        $user->update([
            'password' => $validated['password'],
        ]);

        return back();
    }
}
