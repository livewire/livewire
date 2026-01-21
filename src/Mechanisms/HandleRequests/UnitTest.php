<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_livewire_can_run_handle_request_without_components_on_payload(): void
    {
        $handleRequestsInstance = new HandleRequests();
        $request = new Request();

        $result = $handleRequestsInstance->handleUpdate($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('components', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertIsArray($result['components']);
        $this->assertEmpty($result['components']);
        $this->assertIsArray($result['assets']);
        $this->assertEmpty($result['assets']);
    }

    public function test_default_livewire_update_route_is_registered(): void
    {
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });

        $this->assertCount(1, $livewireUpdateRoutes);
        $this->assertEquals('livewire/update', $livewireUpdateRoutes->first()->uri());
    }

    public function test_duplicate_route_is_not_registered_when_livewire_update_route_already_exists(): void
    {
        // Verify that only one livewire.update route exists initially
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });
        $this->assertCount(1, $livewireUpdateRoutes);

        // Simulate what happens during cached routes scenario: create a new HandleRequests
        // instance (which has $updateRoute = null) and call boot() again
        $newHandleRequests = new HandleRequests();
        $newHandleRequests->boot();

        // Manually trigger the booted callback since we're already past the boot phase
        app()->booted(function () {});

        // Verify that still only one livewire.update route exists (no duplicate)
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });
        $this->assertCount(1, $livewireUpdateRoutes);
    }

    public function test_get_update_uri_works_when_update_route_property_is_null(): void
    {
        // Simulate the cached routes scenario where routes are loaded from cache
        // and HandleRequests::$updateRoute was never set because setUpdateRoute()
        // was not called (the route already existed in the router).
        $handleRequests = new HandleRequests();
        $handleRequests->register();
        $handleRequests->boot();

        // This should work even though $updateRoute is null by finding the route from the router
        $uri = $handleRequests->getUpdateUri();

        $this->assertEquals('/livewire/update', $uri);
    }
}
