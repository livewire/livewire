<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    protected function tearDown(): void
    {
        config()->set('livewire.back_button_cache', false);
        SupportDisablingBackButtonCache::$disableBackButtonCache = null;

        parent::tearDown();
    }

    public function test_ensure_disable_browser_cache_middleware_is_not_applied_to_a_route_that_does_not_contain_a_component()
    {
        Route::get('test-route-without-livewire-component', function () { return 'ok'; });

        $response = $this->get('test-route-without-livewire-component')->assertSuccessful();

        // There are a couple of different headers applied in the middleware,
        // so just testing for one that isn't normally in a Laravel request
        $this->assertFalse($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_ensure_browser_cache_middleware_is_applied_to_a_route_that_contains_a_component_with_disable_set_to_true()
    {
        Route::get('test-route-containing-livewire-component', DisableBrowserCache::class);

        $response = $this->get('test-route-containing-livewire-component')->assertSuccessful();

        // There are a couple of different headers applied in the middleware,
        // so just testing for one that isn't normally in a Laravel request
        $this->assertTrue($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_ensure_disable_browser_cache_middleware_is_disabled_after_a_livewire_request_so_no_following_non_livewire_requests_have_it_enabled()
    {
        Route::get('test-route-containing-livewire-component', DisableBrowserCache::class);
        Route::get('test-route-without-livewire-component', function () { return 'ok'; });

        $response = $this->get('test-route-containing-livewire-component')->assertSuccessful();

        // There are a couple of different headers applied in the middleware,
        // so just testing for one that isn't normally in a Laravel request
        $this->assertTrue($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));

        $response = $this->get('test-route-without-livewire-component')->assertSuccessful();

        // There are a couple of different headers applied in the middleware,
        // so just testing for one that isn't normally in a Laravel request
        $this->assertFalse($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_back_button_cache_can_be_enabled_by_config_for_standard_components()
    {
        config()->set('livewire.back_button_cache', true);
        SupportDisablingBackButtonCache::$disableBackButtonCache = null;

        Route::get('test-route-containing-livewire-component', DefaultBrowserCache::class);

        $response = $this->get('test-route-containing-livewire-component')->assertSuccessful();

        $this->assertFalse($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_back_button_cache_can_still_be_disabled_per_component_when_config_enables_it()
    {
        config()->set('livewire.back_button_cache', true);
        SupportDisablingBackButtonCache::$disableBackButtonCache = null;

        Route::get('test-route-containing-livewire-component', DisableBrowserCache::class);

        $response = $this->get('test-route-containing-livewire-component')->assertSuccessful();

        $this->assertTrue($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_back_button_cache_stays_disabled_when_a_later_component_does_not_opt_in()
    {
        config()->set('livewire.back_button_cache', true);
        SupportDisablingBackButtonCache::$disableBackButtonCache = null;

        Route::get('test-route-containing-livewire-components', function () {
            return Blade::render(<<<'BLADE'
                @livewire(\Livewire\Features\SupportDisablingBackButtonCache\DisableBrowserCache::class)
                @livewire(\Livewire\Features\SupportDisablingBackButtonCache\DefaultBrowserCache::class)
                BLADE);
        });

        $response = $this->get('test-route-containing-livewire-components')->assertSuccessful();

        $this->assertTrue($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }

    public function test_back_button_cache_stays_enabled_when_a_later_component_does_not_opt_out()
    {
        config()->set('livewire.back_button_cache', false);
        SupportDisablingBackButtonCache::$disableBackButtonCache = null;

        Route::get('test-route-containing-livewire-components', function () {
            return Blade::render(<<<'BLADE'
                @livewire(\Livewire\Features\SupportDisablingBackButtonCache\EnableBrowserCache::class)
                @livewire(\Livewire\Features\SupportDisablingBackButtonCache\DefaultBrowserCache::class)
                BLADE);
        });

        $response = $this->get('test-route-containing-livewire-components')->assertSuccessful();

        $this->assertFalse($response->baseResponse->headers->hasCacheControlDirective('must-revalidate'));
    }
}

class DisableBrowserCache extends TestComponent
{
    public function mount()
    {
        $this->disableBackButtonCache();
    }
}

class DefaultBrowserCache extends TestComponent
{
}

class EnableBrowserCache extends TestComponent
{
    public function mount()
    {
        $this->enableBackButtonCache();
    }
}
