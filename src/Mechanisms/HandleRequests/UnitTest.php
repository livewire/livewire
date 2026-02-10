<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_missing_components_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), []);

        $response->assertNotFound();
    }

    public function test_empty_components_array_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertNotFound();
    }

    public function test_non_array_components_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => 'not-an-array']);

        $response->assertNotFound();
    }

    public function test_component_missing_snapshot_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_component_with_non_string_snapshot_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => 123, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_component_missing_updates_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => '{}', 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_component_missing_calls_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => '{}', 'updates' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_component_with_non_array_updates_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => '{}', 'updates' => 'bad', 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_component_with_non_array_calls_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => '{}', 'updates' => [], 'calls' => 'bad'],
            ]]);

        $response->assertNotFound();
    }

    public function test_snapshot_decoding_to_null_returns_404(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => 'not-valid-json', 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_decoded_snapshot_missing_data_returns_404(): void
    {
        $snapshot = json_encode(['memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_decoded_snapshot_missing_memo_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_decoded_snapshot_missing_checksum_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo']]);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_decoded_snapshot_memo_missing_id_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_decoded_snapshot_memo_missing_name_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public function test_call_missing_method_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => [
                    ['params' => []],
                ]],
            ]]);

        $response->assertNotFound();
    }

    public function test_call_missing_params_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => [
                    ['method' => 'doSomething'],
                ]],
            ]]);

        $response->assertNotFound();
    }

    public function test_call_with_non_string_method_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => [
                    ['method' => 123, 'params' => []],
                ]],
            ]]);

        $response->assertNotFound();
    }

    public function test_call_with_non_array_params_returns_404(): void
    {
        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => [
                    ['method' => 'doSomething', 'params' => 'bad'],
                ]],
            ]]);

        $response->assertNotFound();
    }

    public function test_bad_checksum_returns_419(): void
    {
        Livewire::component('schema-test', SchemaValidationTestComponent::class);

        $snapshot = json_encode([
            'data' => [],
            'memo' => ['id' => 'abc', 'name' => 'schema-test'],
            'checksum' => 'invalid-checksum-value',
        ]);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertStatus(419);
    }

    public function test_valid_request_returns_200(): void
    {
        Livewire::component('schema-test', SchemaValidationTestComponent::class);

        $testable = Livewire::test(SchemaValidationTestComponent::class);
        $snapshotJson = json_encode($testable->snapshot);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertOk();
        $this->assertArrayHasKey('components', $response->json());
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

        // Livewire's update route should still be matched (returns 404 from
        // schema validation, not 200 "catch-all" from the catch-all route)
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertNotFound();
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

class SchemaValidationTestComponent extends Component
{
    public function render()
    {
        return '<div></div>';
    }
}
