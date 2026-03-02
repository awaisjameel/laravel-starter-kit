<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\ProfileDestroyRequest;
use App\Modules\Settings\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('modules/settings/pages/Profile', [
            'mustVerifyEmail' => $user !== null
                && in_array(MustVerifyEmail::class, class_implements($user), true),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $profileUpdateRequest): RedirectResponse
    {
        $user = $profileUpdateRequest->user();

        if ($user === null) {
            return redirect()->route('auth.login.create');
        }

        $profileUpdateData = $profileUpdateRequest->toDto();

        $user->fill([
            'name' => $profileUpdateData->name,
            'email' => $profileUpdateData->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('app.settings.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDestroyRequest $profileDestroyRequest): RedirectResponse
    {
        $user = $profileDestroyRequest->user();

        Auth::logout();

        $user?->delete();

        $profileDestroyRequest->session()->invalidate();
        $profileDestroyRequest->session()->regenerateToken();

        return redirect('/');
    }
}
