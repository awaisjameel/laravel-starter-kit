<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Appearance;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Override;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    #[Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function share(Request $request): array
    {
        $quote = Inspiring::quotes()->random();
        $quoteText = is_string($quote) ? $quote : '';
        $quoteParts = explode('-', $quoteText, 2);
        $message = $quoteParts[0];
        $author = $quoteParts[1] ?? '';

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => mb_trim($message), 'author' => mb_trim($author)],
            'auth' => [
                'user' => $request->user()?->toViewData(),
            ],
            'flash' => [
                'message' => $request->session()->get('message'),
                'error' => $request->session()->get('error'),
                'status' => $request->session()->get('status'),
            ],
            // The absolute request URL. Inertia's own `page.url` is relative, so the
            // frontend needs an origin to resolve it against, and `fullUrl()` keeps
            // the query string: server-driven listing pages rehydrate their table
            // state (page, search, sort) from it, and dropping the query would
            // silently reset a shared or bookmarked URL to the default sort while
            // the rendered rows stayed filtered.
            'location' => $request->fullUrl(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Same value `HandleAppearance` puts on the root element, so the appearance
            // UI renders correctly on the server and hydrates without a mismatch.
            'appearance' => Appearance::fromCookie($request->cookie(Appearance::COOKIE)),
        ];
    }
}
