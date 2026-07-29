<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('security headers are present on web responses', function (): void {
    $testResponse = $this->get('/');

    $contentSecurityPolicy = (string) $testResponse->headers->get('Content-Security-Policy');

    $this->assertNotSame('', $contentSecurityPolicy);
    expect($contentSecurityPolicy)->toMatch("/script-src 'self' 'nonce-[^']+'/");
    $testResponse->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $testResponse->assertHeader('X-Content-Type-Options', 'nosniff');
    $testResponse->assertHeader('X-Frame-Options', 'DENY');
    $testResponse->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});
