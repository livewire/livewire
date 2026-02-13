<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

use Livewire\Livewire;
use Livewire\LivewireManager;
use function Livewire\trigger;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        \Livewire\LivewireManager::$v4 = false;

        parent::setUp();
    }

    public function tearDown(): void
    {
        // Clean up any published assets after each test
        if (file_exists(public_path('vendor/livewire'))) {
            File::deleteDirectory(public_path('vendor/livewire'));
        }

        parent::tearDown();
    }

    public function test_styles()
    {
        $assets = app(FrontendAssets::class);

        $this->assertFalse($assets->hasRenderedStyles);

        $styles = $assets->styles();

        $this->assertStringStartsWith('<!-- Livewire Styles -->', $styles);
        $this->assertStringNotContainsString('data-livewire-style', $styles);

        $this->assertTrue($assets->hasRenderedStyles);

        $this->assertEmpty($assets->styles());
    }

    public function test_styles_with_nonce()
    {
        $assets = app(FrontendAssets::class);

        $this->assertStringContainsString('nonce="test" data-livewire-style', $assets->styles(['nonce' => 'test']));
    }

    public function test_scripts()
    {
        $assets = app(FrontendAssets::class);

        $this->assertFalse($assets->hasRenderedScripts);

        $scripts = $assets->scripts();
        $this->assertStringContainsString('<script src="', $scripts);

        $this->assertTrue($assets->hasRenderedScripts);

        $this->assertEmpty($assets->scripts());
    }

    public function test_use_normal_scripts_url_if_app_debug_is_true()
    {
        config()->set('app.debug', true);

        $assets = app(FrontendAssets::class);

        // Call boot again, as the script route has to be set after the config is set
        $assets->boot();

        $this->assertStringContainsString('livewire.js', $assets->scripts());
    }

    public function test_use_minified_scripts_url_if_app_debug_is_false()
    {
        config()->set('app.debug', false);

        $assets = app(FrontendAssets::class);

        // Call boot again, as the script route has to be set after the config is set
        $assets->boot();

        $this->assertStringContainsString('livewire.min.js', $assets->scripts());
    }

    public function test_use_normal_scripts_file_if_app_debug_is_true()
    {
        config()->set('app.debug', true);

        $assets = app(FrontendAssets::class);

        $fileResponse = $assets->returnJavaScriptAsFile();

        $this->assertEquals('livewire.js', $fileResponse->getFile()->getFilename());
    }

    public function test_use_minified_scripts_file_if_app_debug_is_false()
    {
        config()->set('app.debug', false);

        $assets = app(FrontendAssets::class);

        $fileResponse = $assets->returnJavaScriptAsFile();

        $this->assertEquals('livewire.min.js', $fileResponse->getFile()->getFilename());
    }

    public function test_if_script_route_has_been_overridden_use_normal_scripts_file_if_app_debug_is_true()
    {
        config()->set('app.debug', true);

        $assets = app(FrontendAssets::class);

        $assets->setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle);
        });

        $response = $this->get('/livewire/livewire.js');

        $this->assertEquals('livewire.js', $response->getFile()->getFilename());
    }

    public function test_if_script_route_has_been_overridden_use_minified_scripts_file_if_app_debug_is_false()
    {
        config()->set('app.debug', false);

        $assets = app(FrontendAssets::class);

        $assets->setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle);
        });

        $response = $this->get('/livewire/livewire.js');

        $this->assertEquals('livewire.min.js', $response->getFile()->getFilename());
    }

    public function test_flush_state_event_resets_has_rendered()
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

    public function test_js_does_not_prepend_slash_for_url()
    {
        $url = 'https://example.com/livewire/livewire.js';
        $this->assertStringStartsWith('<script src="'.$url, FrontendAssets::js(['url' => $url]));
    }

    public function test_js_prepends_slash_for_non_url()
    {
        $url = 'livewire/livewire.js';
        $this->assertStringStartsWith('<script src="/'.$url, FrontendAssets::js(['url' => $url]));
    }

    public function test_js_does_not_prepend_slash_for_non_standard_protocol_scheme()
    {
        $url = 'php://127.0.0.1/livewire/livewire.js';
        $this->assertStringStartsWith('<script src="'.$url, FrontendAssets::js(['url' => $url]));
    }

    public function test_js_appends_version_with_correct_query_separator()
    {
        // URL without query params should use ?
        $withoutQuery = FrontendAssets::js(['url' => 'https://cdn.example.com/livewire.js']);
        $this->assertMatchesRegularExpression('/livewire\.js\?id=/', $withoutQuery);

        // URL with existing query params should use &
        $withQuery = FrontendAssets::js(['url' => 'https://cdn.example.com/livewire.js?token=abc']);
        $this->assertMatchesRegularExpression('/\?token=abc&id=/', $withQuery);
    }

    public function test_it_returns_published_assets_url_when_running_serverless()
    {
        $assets = app(FrontendAssets::class);

        Artisan::call('livewire:publish', ['--assets' => true]);

        config()->set('app.debug', false);
        config()->set('app.asset_url', 'https://example.com/');

        $manager = $this->partialMock(LivewireManager::class, function ($mock) {
            $mock->shouldReceive('isRunningServerless')
                ->once()
                ->andReturn(true);
        });

        $this->app->instance('livewire', $manager);

        $this->assertStringStartsWith('<script src="https://example.com/vendor/livewire/livewire.min.js', $assets->scripts());

        // Clean up published assets
        if (file_exists(public_path('vendor/livewire'))) {
            File::deleteDirectory(public_path('vendor/livewire'));
        }
    }

    public function test_published_assets_apply_version_to_configured_asset_url()
    {
        $assets = app(FrontendAssets::class);

        Artisan::call('livewire:publish', ['--assets' => true]);

        config()->set('livewire.asset_url', 'https://cdn.example.com/livewire.js');
        config()->set('app.debug', false);

        $scripts = $assets->scripts();

        // Should include version hash from manifest
        $this->assertMatchesRegularExpression(
            '/https:\/\/cdn\.example\.com\/livewire\.js\?id=[a-zA-Z0-9]+/',
            $scripts
        );

        // Should NOT use the default vendor path
        $this->assertStringNotContainsString('vendor/livewire', $scripts);
    }
}
