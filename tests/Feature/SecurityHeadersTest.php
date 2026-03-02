<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $testResponse = $this->get('/');

        $contentSecurityPolicy = (string) $testResponse->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $contentSecurityPolicy);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[^']+'/", $contentSecurityPolicy);
        $testResponse->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $testResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $testResponse->assertHeader('X-Frame-Options', 'DENY');
        $testResponse->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }
}
