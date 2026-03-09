<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $isLocalEnvironment = App::isLocal();

        $scriptSrc = $isLocalEnvironment
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval' http: https:"
            : sprintf("script-src 'self' 'nonce-%s'", $nonce);
        $styleSrc = $isLocalEnvironment
            ? "style-src 'self' 'unsafe-inline' https://fonts.bunny.net"
            : sprintf("style-src 'self' 'nonce-%s' https://fonts.bunny.net", $nonce);
        $fontSrc = "font-src 'self' https://fonts.bunny.net data:";

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            $scriptSrc,
            $styleSrc,
            $fontSrc,
            "img-src 'self' data: blob:",
            "connect-src 'self' ws: wss: http: https:",
            "form-action 'self'",
        ]));
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
