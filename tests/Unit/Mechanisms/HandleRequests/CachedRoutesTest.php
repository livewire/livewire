<?php

namespace Tests\Unit\Mechanisms\HandleRequests;

use Illuminate\Foundation\Testing\WithCachedRoutes;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class CachedRoutesTest extends TestCase
{
    use WithCachedRoutes;

    public function test_get_update_uri_works_with_cached_routes()
    {
        // When routes are cached, HandleRequests::$updateRoute is null because
        // the route already exists in the router and setUpdateRoute() is not called.
        // getUpdateUri() should still work by falling back to route lookup.
        $uri = app('livewire')->getUpdateUri();

        $this->assertEquals('/livewire/update', $uri);
    }

    public function test_livewire_testing_works_with_cached_routes()
    {
        // This test ensures that Livewire::test() works when routes are cached,
        // which requires getUpdateUri() to work correctly.
        Livewire::test(CachedRoutesTestComponent::class)
            ->call('increment')
            ->assertSet('count', 1);
    }

    public function test_get_update_uri_works_when_update_route_is_null()
    {
        // Simulate the scenario where routes are loaded from cache and
        // HandleRequests::$updateRoute was never set. This happens when:
        // 1. Routes are cached and loaded by Laravel
        // 2. HandleRequests::boot() sees the route exists via updateRouteExists()
        // 3. setUpdateRoute() is never called, so $updateRoute remains null

        // Create a fresh HandleRequests instance to simulate fresh boot
        $handleRequests = new HandleRequests;
        $handleRequests->register();

        // The route already exists from the previous test/Livewire boot,
        // so updateRouteExists() will return true and setUpdateRoute() won't be called
        $handleRequests->boot();

        // This should work even though $updateRoute is null
        $uri = $handleRequests->getUpdateUri();

        $this->assertEquals('/livewire/update', $uri);
    }
}

class CachedRoutesTestComponent extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}
