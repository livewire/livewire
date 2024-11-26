<?php

namespace Livewire\Tests;

use Livewire\Livewire;
use Illuminate\Support\Facades\View;

class LivewireAssetsDirectiveUnitTest extends \Tests\TestCase
{
    function setUp(): void
    {
        $this->markTestSkipped('not sure exactly how we want to handle all this asset stuff for v3 so holding off on this...');
    }

    public function test_livewire_js_is_unminified_when_app_is_in_debug_mode()
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

    public function livewire_js_should_use_configured_app_url()
    {
        config()->set('app.debug', true);
        config()->set('livewire.app_url', 'https://foo.com');

        $this->assertStringContainsString(
            '<script src="/livewire/livewire.js?',
            Livewire::scripts()
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo.com';",
            Livewire::scripts()
        );
    }

    public function test_livewire_js_calls_reference_relative_root()
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

    public function test_livewire_js_calls_reference_configured_asset_url()
    {
        $this->assertStringContainsString(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::scripts(['asset_url' => 'https://foo.com/assets'])
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo-bar.com/path';",
            Livewire::scripts(['app_url' => 'https://foo-bar.com/path'])
        );
    }

    public function test_asset_url_trailing_slashes_are_trimmed()
    {
        $this->assertStringContainsString(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            Livewire::scripts(['asset_url' => 'https://foo.com/assets/'])
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://foo.com/assets';",
            Livewire::scripts(['app_url' => 'https://foo.com/assets/'])
        );
    }

    public function test_asset_url_passed_into_blade_assets_directive()
    {
        $output = View::make('assets-directive', [
            'options' => ['asset_url' => 'https://foo.com/assets/', 'app_url' => 'https://bar.com/'],
        ])->render();

        $this->assertStringContainsString(
            '<script src="https://foo.com/assets/livewire/livewire.js?',
            $output
        );

        $this->assertStringContainsString(
            "window.livewire_app_url = 'https://bar.com';",
            $output
        );
    }

    public function test_nonce_passed_into_directive_gets_added_as_script_tag_attribute()
    {
        $output = View::make('assets-directive', [
            'options' => ['nonce' => 'foobarnonce'],
        ])->render();

        $this->assertStringContainsString(
            ' nonce="foobarnonce">',
            $output
        );
    }
}
