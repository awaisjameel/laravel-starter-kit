<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Appearance;
use Inertia\Testing\AssertableInertia;
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

    public function test_unrecognised_cookie_value_falls_back_to_light(): void
    {
        $testResponse = $this->withUnencryptedCookies(['appearance' => 'sepia'])->get('/');

        $testResponse->assertViewHas('appearance', 'light');
    }

    /**
     * The root element and the appearance UI must agree, or the server renders one
     * theme while the client hydrates the controls in the other.
     */
    public function test_appearance_is_shared_with_inertia_as_well_as_the_root_view(): void
    {
        $testResponse = $this->withUnencryptedCookies(['appearance' => 'dark'])->get('/');

        $testResponse->assertInertia(
            fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia->where('appearance', Appearance::Dark->value)
        );
    }

    public function test_root_element_carries_the_dark_class_before_any_script_runs(): void
    {
        $testResponse = $this->withUnencryptedCookies(['appearance' => 'dark'])->get('/');

        $testResponse->assertSee('class="dark"', escape: false);
    }
}
