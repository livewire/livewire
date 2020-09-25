<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Artisan;

class LivewireAssetsDirectiveTest extends TestCase
{
    public function tearDown(): void
    {
        $this->clearPublishedAssets();
        parent::tearDown();
    }

    /** @test */
    public function livewire_js_is_unminified_when_app_is_in_debug_mode()
    {
        config()->set('app.debug', true);

        $this->assertStringContainsString(
            '<script src="/livewire/livewire.js?',
            Livewire::scripts()
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = '';",
            Livewire::scripts()
        );
    }

    /** @test */
    public function livewire_js_calls_reference_relative_root()
    {
        $this->assertStringContainsString(
            '<script src="/livewire/livewire.js?',
            Livewire::scripts()
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = '';",
            Livewire::scripts()
        );
    }

    /** @test */
    public function livewire_js_calls_reference_configured_app_url()
    {
        config()->set('livewire.app_url', 'https://foo.com/app');

        $this->assertStringContainsString(
            '<script src="https://foo.com/app/livewire/livewire.js?',
            Livewire::scripts()
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo.com/app';",
            Livewire::scripts()
        );

        // ensure providing a path works as well
        config()->set('livewire.app_url', '/app');

        $this->assertStringContainsString(
            "window.livewire_app_url = '/app';",
            Livewire::scripts()
        );

        config()->set('livewire.app_url', null);
    }

    /** @test */
    public function livewire_js_calls_reference_provided_app_url_over_configured_app_url()
    {
        config()->set('livewire.app_url', 'https://foo.com/app');

        $this->assertStringContainsString(
            '<script src="https://foo.com/bar/livewire/livewire.js?',
            Livewire::scripts(['app_url' => 'https://foo.com/bar'])
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo.com/bar';",
            Livewire::scripts(['app_url' => 'https://foo.com/bar'])
        );

        config()->set('livewire.app_url', null);
    }

    /** @test */
    public function livewire_js_assets_reference_configured_asset_url_only_when_assets_are_published()
    {
        config()->set('app.asset_url', 'https://foo.com/assets');

        // ensure assets are served from route when assets have not been published
        $this->assertStringNotContainsString(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::scripts()
        );

        $this->assertStringContainsString(
            '<script src="/livewire/livewire.js?',
            Livewire::scripts()
        );

        // publish assets and ensure the static asset uses the configured asset URL
        $this->publishAssets();

        $this->assertStringContainsString(
            '<script src="https://foo.com/assets/vendor/livewire/livewire.js?',
            Livewire::scripts()
        );

        // ensure asset_url does not affect the app url
        $this->assertStringNotContainsString(
            "window.livewire_app_url = 'https://foo.com/assets';",
            Livewire::scripts()
        );

        $this->clearPublishedAssets();

        config()->set('app.asset_url', null);
    }

    /** @test */
    public function app_and_asset_url_trailing_slashes_are_trimmed()
    {
        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo.com/app';",
            Livewire::scripts(['app_url' => 'https://foo.com/app/'])
        );

        config()->set('app.asset_url', 'https://foo.com/assets/');
        $this->publishAssets();

        $this->assertStringContainsString(
            '<script src="https://foo.com/assets/vendor/livewire/livewire.js?',
            Livewire::scripts()
        );

        $this->clearPublishedAssets();

        config()->set('app.asset_url', null);
    }

    /** @test */
    public function app_url_passed_into_blade_assets_directive()
    {
        $output = View::make('assets-directive', [
            'options' => ['app_url' => 'https://foo.com/app/'],
        ])->render();

        $this->assertStringContainsString(
            '<script src="https://foo.com/app/livewire/livewire.js?',
            $output
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo.com/app';",
            $output
        );
    }

    /** @test */
    public function nonce_passed_into_directive_gets_added_as_script_tag_attribute()
    {
        $output = View::make('assets-directive', [
            'options' => ['nonce' => 'foobarnonce'],
        ])->render();

        $this->assertStringContainsString(
            'nonce="foobarnonce">',
            $output
        );
    }

    private function publishAssets()
    {
        Artisan::call('livewire:publish', [
            '--assets' => true
        ]);

        $this->published_assets = public_path() . DIRECTORY_SEPARATOR . 'vendor';
    }

    private function clearPublishedAssets()
    {
        if (isset($this->published_assets)) {
            shell_exec('rm -rf ' . $this->published_assets);
        }
    }
}
