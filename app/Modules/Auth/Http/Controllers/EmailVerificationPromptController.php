<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Data\VerifyEmailPageData;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

final class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = RequestActor::from($request);
        $status = $request->session()->get('status');

        return $user->hasVerifiedEmail()
            ? redirect()->intended(route('app.dashboard', absolute: false))
            : PageResponder::render(
                'modules/auth/pages/VerifyEmail',
                new VerifyEmailPageData(
                    status: is_string($status) ? $status : null,
                ),
            );
    }
}
