<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
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
        $this->assertEquals(ltrim(EndpointResolver::updatePath(), '/'), $livewireUpdateRoutes->first()->uri());
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

        // Verify that still only one livewire.update route exists (no duplicate)
        // The updateRouteExists() check in boot() prevents duplicate registration
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });
        $this->assertCount(1, $livewireUpdateRoutes);
    }

    public function test_catch_all_route_does_not_intercept_livewire_update_requests(): void
    {
        // Register a catch-all route (simulating what happens in routes files)
        Route::any('{all?}', function () {
            return 'catch-all';
        })->where('all', '.*');

        // Livewire's update route should still be matched
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->post(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertOk();
        $this->assertArrayHasKey('components', $response->json());
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

        $this->assertEquals(EndpointResolver::updatePath(), $uri);
    }

    public function test_get_update_uri_works_when_route_name_is_not_indexed(): void
    {
        // Force-resolve the URL generator singleton first to prevent
        // its initial resolution from rebuilding the nameList.
        app('url');

        // Simulate a scenario where the route exists in the router's allRoutes
        // but isn't indexed in the RouteCollection's nameList. This can happen
        // when ->name() executes after RouteCollection::add() and the name
        // lookups aren't refreshed (e.g., due to Octane worker resets).
        $routes = Route::getRoutes();
        $nameListProperty = new ReflectionProperty($routes, 'nameList');
        $nameListProperty->setAccessible(true);
        $nameList = $nameListProperty->getValue($routes);
        unset($nameList['default.livewire.update']);
        $nameListProperty->setValue($routes, $nameList);

        // Verify the route is still findable by iteration but not by name lookup
        $found = collect($routes->getRoutes())
            ->first(fn ($route) => str($route->getName())->endsWith('livewire.update'));
        $this->assertNotNull($found);
        $this->assertNull($routes->getByName('default.livewire.update'));

        // getUpdateUri() should still work despite the name not being indexed
        $handleRequests = app(HandleRequests::class);
        $uri = $handleRequests->getUpdateUri();

        $this->assertEquals(EndpointResolver::updatePath(), $uri);
    }
}
