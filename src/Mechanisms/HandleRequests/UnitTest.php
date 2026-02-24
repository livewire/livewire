<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    #[DataProvider('malformedRequestPayloads')]
    public function test_malformed_request_payload_returns_404($payload): void
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), $payload);

        $response->assertNotFound();
    }

    public static function malformedRequestPayloads()
    {
        return [
            'missing components' => [[]],
            'empty components' => [['components' => []]],
            'non-array components' => [['components' => 'not-an-array']],
            'missing snapshot' => [['components' => [['updates' => [], 'calls' => []]]]],
            'non-string snapshot' => [['components' => [['snapshot' => 123, 'updates' => [], 'calls' => []]]]],
            'missing updates' => [['components' => [['snapshot' => '{}', 'calls' => []]]]],
            'missing calls' => [['components' => [['snapshot' => '{}', 'updates' => []]]]],
            'non-array updates' => [['components' => [['snapshot' => '{}', 'updates' => 'bad', 'calls' => []]]]],
            'non-array calls' => [['components' => [['snapshot' => '{}', 'updates' => [], 'calls' => 'bad']]]],
        ];
    }

    #[DataProvider('malformedSnapshots')]
    public function test_malformed_snapshot_returns_404($snapshot): void
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertNotFound();
    }

    public static function malformedSnapshots()
    {
        return [
            'invalid json' => ['not-valid-json'],
            'missing data' => [json_encode(['memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash'])],
            'missing memo' => [json_encode(['data' => [], 'checksum' => 'hash'])],
            'missing checksum' => [json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo']])],
            'missing memo.id' => [json_encode(['data' => [], 'memo' => ['name' => 'foo'], 'checksum' => 'hash'])],
            'missing memo.name' => [json_encode(['data' => [], 'memo' => ['id' => 'abc'], 'checksum' => 'hash'])],
        ];
    }

    #[DataProvider('malformedCalls')]
    public function test_malformed_calls_returns_404($calls): void
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        $snapshot = json_encode(['data' => [], 'memo' => ['id' => 'abc', 'name' => 'foo'], 'checksum' => 'hash']);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => $calls],
            ]]);

        $response->assertNotFound();
    }

    public static function malformedCalls()
    {
        return [
            'missing method' => [[['params' => []]]],
            'missing params' => [[['method' => 'doSomething']]],
            'non-string method' => [[['method' => 123, 'params' => []]]],
            'non-array params' => [[['method' => 'doSomething', 'params' => 'bad']]],
        ];
    }

    public function test_bad_checksum_returns_419(): void
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        $testable = Livewire::test(new class extends TestComponent {});

        $snapshot = json_encode([
            'data' => [],
            'memo' => [
                'id' => 'abc',
                'name' => $testable->snapshot['memo']['name'],
                'release' => $testable->snapshot['memo']['release'],
            ],
            'checksum' => 'invalid-checksum-value',
        ]);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertStatus(419);
    }

    public function test_type_mismatched_update_value_returns_419(): void
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        $testable = Livewire::test(new class extends TestComponent {
            public array $items = [];
        });

        $snapshotJson = json_encode($testable->snapshot);

        // Send a string where an array property is expected...
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => ['items' => 'not_an_array'], 'calls' => []],
            ]]);

        $response->assertStatus(419);
    }

    public function test_valid_request_returns_200(): void
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        $testable = Livewire::test(new class extends TestComponent {});
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

        $testable = Livewire::test(new class extends TestComponent {});
        $snapshotJson = json_encode($testable->snapshot);

        // Livewire's update route should still be matched, not the catch-all
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => [], 'calls' => []],
            ]]);

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
        $testable = Livewire::test(new class extends TestComponent {});
        $snapshotJson = json_encode($testable->snapshot);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertOk();
        $this->assertArrayHasKey('components', $response->json());
    }

    /**
     * When a public property contains non-UTF-8 bytes (e.g. from a latin1/Windows-1252 database),
     * json_encode() fails. Instead of silently returning "snapshot": false (which causes
     * a cryptic JS error "f.memo is undefined" on the client), a SnapshotEncodingException
     * should be thrown with a clear message pointing to the actual problem.
     */
    public function test_snapshot_json_encode_failure_throws_exception(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Livewire\Exceptions\SnapshotEncodingException::class);

        $testable = Livewire::test(new class extends TestComponent {
            public array $items = [];

            public function loadItems()
            {
                // 0x92 = right single quotation mark in Windows-1252, invalid UTF-8 byte
                $this->items = [
                    ['name' => "Test\x92s Item"],
                ];
            }
        });

        $snapshotJson = json_encode($testable->snapshot);

        // This request triggers loadItems(), which sets a non-UTF-8 property.
        // When the snapshot is serialized for the response, json_encode fails
        // and a SnapshotEncodingException should be thrown.
        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => [], 'calls' => [
                    ['method' => 'loadItems', 'params' => []],
                ]],
            ]]);
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
