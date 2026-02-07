<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Models\User;
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
        return Inertia::render('settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $passwordUpdateRequest): RedirectResponse
    {
        $user = $passwordUpdateRequest->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $validated = $passwordUpdateRequest->validated();

        $user->update([
            'password' => $validated['password'],
        ]);

        return back();
    }
}
