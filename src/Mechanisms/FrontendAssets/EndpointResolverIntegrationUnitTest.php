<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireServiceProvider;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\HandleRequests\EndpointResolverInterface;
use Tests\TestCase;

class EndpointResolverIntegrationUnitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            CustomEndpointResolverServiceProvider::class,
        ];
    }

    public function test_livewire_routes_use_custom_endpoint_resolver()
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        foreach ([
            'custom-livewire/update',
            config('app.debug') ? 'custom-livewire/livewire.js' : 'custom-livewire/livewire.min.js',
            'custom-livewire/livewire.min.js.map',
            'custom-livewire/livewire.csp.min.js.map',
            'custom-livewire/upload-file',
            'custom-livewire/preview-file/{filename}',
            'custom-livewire/js/{component}.js',
            'custom-livewire/css/{component}.css',
            'custom-livewire/css/{component}.global.css',
        ] as $uri) {
            $this->assertTrue(
                $routes->contains(fn ($route) => $route->uri() === $uri),
                "Expected Livewire route [{$uri}] to be registered."
            );
        }
    }

    public function test_script_route_uses_endpoint_resolver_path()
    {
        $expectedPath = EndpointResolver::scriptPath(minified: !config('app.debug'));

        $frontendAssets = app(FrontendAssets::class);
        $actualPath = '/' . ltrim($frontendAssets->javaScriptRoute->uri, '/');

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function test_script_url_in_html_matches_registered_route()
    {
        $frontendAssets = app(FrontendAssets::class);
        $routeUri = '/' . ltrim($frontendAssets->javaScriptRoute->uri, '/');

        $html = FrontendAssets::scripts();

        // Extract src from script tag (now a full URL, so parse the path)
        preg_match('/src="([^"?]+)/', $html, $matches);
        $srcUrl = $matches[1] ?? '';
        $srcPath = parse_url($srcUrl, PHP_URL_PATH);

        $this->assertEquals($routeUri, $srcPath);
    }

    public function test_update_uri_uses_endpoint_resolver_path()
    {
        $expectedPath = EndpointResolver::updatePath();
        $actualPath = app('livewire')->getUpdateUri();

        $this->assertEquals($expectedPath, $actualPath);
    }

    public function test_all_endpoints_use_same_prefix()
    {
        $prefix = EndpointResolver::prefix();

        $this->assertStringStartsWith($prefix, EndpointResolver::updatePath());
        $this->assertStringStartsWith($prefix, EndpointResolver::scriptPath(false));
        $this->assertStringStartsWith($prefix, EndpointResolver::scriptPath(true));
        $this->assertStringStartsWith($prefix, EndpointResolver::mapPath(false));
        $this->assertStringStartsWith($prefix, EndpointResolver::mapPath(true));
        $this->assertStringStartsWith($prefix, EndpointResolver::uploadPath());
        $this->assertStringStartsWith($prefix, EndpointResolver::previewPath());
    }
}

class CustomEndpointResolverServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            EndpointResolverInterface::class,
            CustomEndpointResolver::class,
        );
    }
}

class CustomEndpointResolver implements EndpointResolverInterface
{
    public function prefix(): string
    {
        return '/custom-livewire';
    }

    public function updatePath(): string
    {
        return $this->prefix() . '/update';
    }

    public function scriptPath(bool $minified = false): string
    {
        $file = $minified ? 'livewire.min.js' : 'livewire.js';

        return $this->prefix() . '/' . $file;
    }

    public function mapPath(bool $csp = false): string
    {
        $file = $csp ? 'livewire.csp.min.js.map' : 'livewire.min.js.map';

        return $this->prefix() . '/' . $file;
    }

    public function uploadPath(): string
    {
        return $this->prefix() . '/upload-file';
    }

    public function previewPath(): string
    {
        return $this->prefix() . '/preview-file/{filename}';
    }

    public function componentJsPath(): string
    {
        return $this->prefix() . '/js/{component}.js';
    }

    public function componentCssPath(): string
    {
        return $this->prefix() . '/css/{component}.css';
    }

    public function componentGlobalCssPath(): string
    {
        return $this->prefix() . '/css/{component}.global.css';
    }
}
