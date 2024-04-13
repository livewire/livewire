<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Route;

use PHPUnit\Framework\Attributes\Test;
use function Livewire\trigger;

class UnitTest extends \Tests\TestCase
{
    #[Test]
    public function styles()
    {
        $assets = app(FrontendAssets::class);

        $this->assertFalse($assets->hasRenderedStyles);

        $this->assertStringStartsWith('<!-- Livewire Styles -->', $assets->styles());

        $this->assertStringNotContainsString('data-livewire-style', $assets->styles());

        $this->assertStringContainsString('nonce="test" data-livewire-style', $assets->styles(['nonce' => 'test']));

        $this->assertTrue($assets->hasRenderedStyles);
    }

    #[Test]
    public function scripts()
    {
        $assets = app(FrontendAssets::class);

        $this->assertFalse($assets->hasRenderedScripts);

        $this->assertStringStartsWith('<script src="', $assets->scripts());

        $this->assertTrue($assets->hasRenderedScripts);
    }

    #[Test]
    public function use_normal_scripts_url_if_app_debug_is_true()
    {
        config()->set('app.debug', true);

        $assets = app(FrontendAssets::class);

        // Call boot again, as the script route has to be set after the config is set
        $assets->boot();

        $this->assertStringContainsString('livewire.js', $assets->scripts());
    }

    #[Test]
    public function use_minified_scripts_url_if_app_debug_is_false()
    {
        config()->set('app.debug', false);

        $assets = app(FrontendAssets::class);

        // Call boot again, as the script route has to be set after the config is set
        $assets->boot();

        $this->assertStringContainsString('livewire.min.js', $assets->scripts());
    }

    #[Test]
    public function use_normal_scripts_file_if_app_debug_is_true()
    {
        config()->set('app.debug', true);

        $assets = app(FrontendAssets::class);

        $fileResponse = $assets->returnJavaScriptAsFile();

        $this->assertEquals('livewire.js', $fileResponse->getFile()->getFilename());
    }

    #[Test]
    public function use_minified_scripts_file_if_app_debug_is_false()
    {
        config()->set('app.debug', false);

        $assets = app(FrontendAssets::class);

        $fileResponse = $assets->returnJavaScriptAsFile();

        $this->assertEquals('livewire.min.js', $fileResponse->getFile()->getFilename());
    }

    #[Test]
    public function if_script_route_has_been_overridden_use_normal_scripts_file_if_app_debug_is_true()
    {
        config()->set('app.debug', true);

        $assets = app(FrontendAssets::class);

        $assets->setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle);
        });

        $response = $this->get('/livewire/livewire.js');

        $this->assertEquals('livewire.js', $response->getFile()->getFilename());
    }

    #[Test]
    public function if_script_route_has_been_overridden_use_minified_scripts_file_if_app_debug_is_false()
    {
        config()->set('app.debug', false);

        $assets = app(FrontendAssets::class);

        $assets->setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle);
        });

        $response = $this->get('/livewire/livewire.js');

        $this->assertEquals('livewire.min.js', $response->getFile()->getFilename());
    }

    #[Test]
    public function flush_state_event_resets_has_rendered()
    {
        $assets = app(FrontendAssets::class);

        $assets->styles();
        $assets->scripts();

        $this->assertTrue($assets->hasRenderedStyles);
        $this->assertTrue($assets->hasRenderedScripts);

        trigger('flush-state');

        $this->assertFalse($assets->hasRenderedScripts);
        $this->assertFalse($assets->hasRenderedStyles);
    }

    #[Test]
    public function js_does_not_prepend_slash_for_url()
    {
        $url = 'https://example.com/livewire/livewire.js';
        $this->assertStringStartsWith('<script src="'.$url, FrontendAssets::js(['url' => $url]));
    }

    public function js_prepends_slash_for_non_url()
    {
        $url = 'livewire/livewire.js';
        $this->assertStringStartsWith('<script src="/'.$url, FrontendAssets::js(['url' => $url]));
    }
}
