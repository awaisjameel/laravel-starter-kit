<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class AppearancePreferenceTest extends TestCase
{
    public function test_default_appearance_is_light_when_cookie_is_missing(): void
    {
        $testResponse = $this->get('/');

        $testResponse->assertViewHas('appearance', 'light');
    }

    public function test_cookie_appearance_value_is_shared_with_the_root_view(): void
    {
        $testResponse = $this->withUnencryptedCookies(['appearance' => 'dark'])->get('/');

        $testResponse->assertViewHas('appearance', 'dark');
    }
}
