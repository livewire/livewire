<?php

namespace Tests;

use Livewire\Livewire;

class LivewireUsesProperAppAndAssetsPathTest extends TestCase
{
    /** @test */
    public function livewire_dot_js_references_configured_app_url()
    {
        $this->assertContains(
            '<script src="/livewire/livewire.js?',
            Livewire::assets()
        );

        config()->set('app.url', 'https://foo.com/assets');

        $this->assertContains(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::assets()
        );
    }

    /** @test */
    public function assets_url_trims_trailing_slash()
    {
        config()->set('app.url', 'https://foo.com/assets/');

        $this->assertContains(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::assets()
        );
    }

    /** @test */
    public function livewire_message_endpoint_uses_configured_app_url()
    {
        config()->set('app.url', 'https://foo.com/app');

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/app";',
            Livewire::assets()
        );
    }

    /** @test */
    public function livewire_message_endpoint_trims_trailing_slash()
    {
        config()->set('app.url', 'https://foo.com/app/');

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/app";',
            Livewire::assets()
        );
    }

    /** @test */
    public function livewire_message_domain_is_ambiguous_if_app_uses_dot_env_default_for_app_url()
    {
        // The scenario for this behavior:
        // An app is created using `laravel new`, but the is served with valet,
        // Livewire would look for: http://localhost/livewire/message and would
        // throw an error.

        config()->set('app.url', 'http://localhost');

        $this->assertContains(
            'window.livewire_app_url = "";',
            Livewire::assets()
        );
    }

    /** @test */
    public function livewires_app_url_default_is_current_with_latest_laravel_version()
    {
        $laravelAppConfigFileContents = file_get_contents(__DIR__.'/../vendor/orchestra/testbench-core/laravel/config/app.php');

        $this->assertContains(
            "'url' => env('APP_URL', 'http://localhost'),",
            $laravelAppConfigFileContents
        );
    }

    /** @test */
    public function livewires_base_app_url_can_be_passed_into_blade_component()
    {
        // I couldn't get your tests running on my machine, but this ***should*** work
        config()->set('app.url', 'https://foo.com/app/');

        $this->assertContains(
            'window.livewire_app_url = "https://passedin.domain.com";',
            Livewire::assets(['base_url' => 'https://passedin.domain.com'])
        );

        // It would be good to send the base_url through the @livewireAssets component as well, but I'm not sure how to test that
    }
}
