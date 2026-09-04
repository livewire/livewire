<?php

namespace Livewire\Tests;

use Error;
use Exception;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireServiceProvider;
use Livewire\Mechanisms\HandleRequests\EndpointResolverInterface;
use Tests\TestCase;

class LivewireRouteCachingUnitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CustomEndpointResolverServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    public function test_custom_livewire_script_route_is_cacheable(): void
    {
        $uri = config('app.debug') ? 'custom-livewire/livewire.js' : 'custom-livewire/livewire.min.js';
        $route = $this->getRoute($uri);

        $this->cacheRoute($route, 'Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile', "Failed to cache route '$uri'");
    }

    public function test_custom_livewire_update_route_is_cacheable(): void
    {
        $uri = 'custom-livewire/update';
        $route = $this->getRoute($uri);

        $this->cacheRoute($route, 'Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate', "Failed to cache route '$uri'");
    }

    public function test_livewire_script_route_is_cacheable(): void
    {
        // The route changes based on debug mode
        $uri = ltrim(\Livewire\Mechanisms\HandleRequests\EndpointResolver::scriptPath(! config('app.debug')), '/');
        $route = $this->getRoute($uri);

        $this->cacheRoute($route, 'Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile', "Failed to cache route '$uri'");
    }

    public function test_livewire_update_route_is_cacheable(): void
    {
        $uri = ltrim(\Livewire\Mechanisms\HandleRequests\EndpointResolver::updatePath(), '/');
        $route = $this->getRoute($uri);

        $this->cacheRoute($route, 'Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate', "Failed to cache route 'livewire/update'");
    }

    protected function getRoute(string $uri): Route
    {
        $route = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->firstWhere(fn(Route $route) => $route->uri() === $uri);

        if ($route === null) {
            $this->fail("Route '$uri' not found.");
        }

        return $route;
    }

    protected function cacheRoute(Route $route, string $expectedHandle, string $message): void
    {
        try {
            $route->prepareForSerialization();

            $this->assertStringContainsString($expectedHandle, $route->getAction('uses'));
        } catch (Error|Exception) {
            $this->fail($message);
        }

        $this->assertTrue(true);
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
