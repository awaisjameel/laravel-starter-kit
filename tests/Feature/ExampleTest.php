<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $testResponse = $this->get('/');

        $testResponse->assertStatus(200);
        $testResponse->assertInertia(fn (Assert $page): Assert => $page->component('marketing/Welcome'));
    }
}
