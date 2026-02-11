<?php

use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_livewire_can_run_handle_request_without_components_on_payload(): void
    {
        $handleRequestsInstance = new HandleRequests();

        // Set the required headers on the container's request instance...
        request()->headers->set('X-Livewire', '1');
        request()->headers->set('Content-Type', 'application/json');

        $result = $handleRequestsInstance->handleUpdate();

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
            ->postJson(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertOk();
        $this->assertArrayHasKey('components', $response->json());
    }

    public function test_update_endpoint_returns_404_without_x_livewire_header(): void
    {
        $response = $this->postJson(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertNotFound();
    }

    public function test_update_endpoint_returns_404_without_json_content_type(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->post(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertNotFound();
    }

    public function test_update_endpoint_returns_404_without_either_required_header(): void
    {
        $response = $this->post(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertNotFound();
    }

    public function test_update_endpoint_succeeds_with_required_headers(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => []]);

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
        // Resolve URL generator first to prevent its initial resolution
        // from rebuilding the nameList via refreshNameLookups().
        app('url');

        // Remove the route from nameList while keeping it in allRoutes.
        // This simulates Octane worker resets where ->name() runs after
        // RouteCollection::add() and nameList isn't refreshed.
        $routes = Route::getRoutes();
        $nameListProperty = new ReflectionProperty($routes, 'nameList');
        $nameList = $nameListProperty->getValue($routes);
        unset($nameList['default.livewire.update']);
        $nameListProperty->setValue($routes, $nameList);

        // Route findable by iteration but not by name lookup...
        $found = collect($routes->getRoutes())
            ->first(fn ($route) => str($route->getName())->endsWith('livewire.update'));
        $this->assertNotNull($found);
        $this->assertNull($routes->getByName('default.livewire.update'));

        // getUpdateUri() should still work...
        $handleRequests = app(HandleRequests::class);
        $uri = $handleRequests->getUpdateUri();

        $this->assertEquals(EndpointResolver::updatePath(), $uri);
    }
}
