<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Illuminate\Support\Facades\Route;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_ensure_disable_browser_cache_middleware_is_not_applied_to_a_route_that_does_not_contain_a_component()
    {
        $this->markTestSkipped(); // @todo: Josh Hanley?

        Route::get('test-route-without-livewire-component', function () { return 'ok'; });

        $response = $this->get('test-route-without-livewire-component');

        // There are a couple of different headers applied in the middleware,
        // so just testing for one that isn't normally in a Laravel request
        $this->assertFalse($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_ensure_browser_cache_middleware_is_applied_to_a_route_that_contains_a_component_with_disable_set_to_true()
    {
        Route::get('test-route-containing-livewire-component', DisableBrowserCache::class);

        $response = $this->get('test-route-containing-livewire-component');

        // There are a couple of different headers applied in the middleware,
        // so just testing for one that isn't normally in a Laravel request
        $this->assertTrue($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }
}

class DisableBrowserCache extends TestComponent
{
    public function mount()
    {
        $this->disableBackButtonCache();
    }
}

