<?php

namespace Tests;

use Livewire\Livewire;

class LivewireUsesProperAppAndAssetsPathTest extends TestCase
{
    /** @test */
    function livewire_dot_js_references_configured_app_url()
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
    function assets_url_trims_trailing_slash()
    {
        config()->set('app.url', 'https://foo.com/assets/');

        $this->assertContains(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::assets()
        );
    }

    /** @test */
    function livewire_message_endpoint_uses_configured_app_url()
    {
        config()->set('app.url', 'https://foo.com/app');

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/app";',
            Livewire::assets()
        );
    }

    /** @test */
    function livewire_message_endpoint_trims_trailing_slash()
    {
        config()->set('app.url', 'https://foo.com/app/');

        $this->assertContains(
            'window.livewire_app_url = "https://foo.com/app";',
            Livewire::assets()
        );
    }

    /** @test */
    function livewire_message_domain_is_ambiguous_if_app_uses_dot_env_default_for_app_url()
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
    function livewires_app_url_default_is_current_with_latest_laravel_version()
    {
        $laravelAppConfigFileContents = file_get_contents(__DIR__ . '/../vendor/orchestra/testbench-core/laravel/config/app.php');

        $this->assertContains(
            "'url' => env('APP_URL', 'http://localhost'),",
            $laravelAppConfigFileContents
        );
    }
}
