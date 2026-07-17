<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Livewire\Livewire;
use Livewire\Exceptions\PayloadTooLargeException;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\HandleRequests\DecodeGzipRequests;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders;
use Livewire\Mechanisms\HandleRequests\SnapshotStateStore;
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

    public function test_bad_checksum_exception_is_not_reported_when_debug_is_disabled(): void
    {
        config()->set('app.debug', false);

        $reported = [];
        app(ExceptionHandler::class)
            ->reportable(function (CorruptComponentPayloadException $e) use (&$reported) {
                $reported[] = $e;

                return false;
            });

        app(ExceptionHandler::class)->report(new CorruptComponentPayloadException);

        $this->assertEmpty($reported);
    }

    public function test_bad_checksum_exception_is_reported_when_debug_is_enabled(): void
    {
        config()->set('app.debug', true);

        $reported = [];
        app(ExceptionHandler::class)
            ->reportable(function (CorruptComponentPayloadException $e) use (&$reported) {
                $reported[] = $e;

                return false;
            });

        app(ExceptionHandler::class)->report(new CorruptComponentPayloadException);

        $this->assertCount(1, $reported);
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

    public function test_tampered_property_type_is_not_reported(): void
    {
        config()->set('app.debug', false);

        $reported = [];
        app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
                return false;
            });

        $testable = Livewire::test(new class extends TestComponent {
            public array $items = [];
        });

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => json_encode($testable->snapshot), 'updates' => ['items' => 'not_an_array'], 'calls' => []],
            ]]);

        $response->assertStatus(419);
        $this->assertEmpty($reported, 'Scanner-tampered property assignment should not be reported.');
    }

    public function test_legitimate_typeerror_is_reported(): void
    {
        config()->set('app.debug', false);

        $reported = [];
        app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->reportable(function (\Throwable $e) use (&$reported) {
                $reported[] = $e;
                return false;
            });

        $testable = Livewire::test(new class extends TestComponent {
            public function bug(): int
            {
                return 'not_an_int';
            }
        });

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                [
                    'snapshot' => json_encode($testable->snapshot),
                    'updates' => [],
                    'calls' => [['method' => 'bug', 'params' => [], 'metadata' => []]],
                ],
            ]]);

        $response->assertStatus(419);
        $this->assertNotEmpty($reported, 'Legitimate TypeError from component method body should be reported.');
        $this->assertInstanceOf(\TypeError::class, $reported[0]);
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
        $this->assertSame(
            DecodeGzipRequests::class,
            $livewireUpdateRoutes->first()->middleware()[0],
        );
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

    public function test_snapshot_json_encode_failure_throws_exception(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\JsonException::class);

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

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => [], 'calls' => [
                    ['method' => 'loadItems', 'params' => []],
                ]],
            ]]);
    }

    public function test_require_livewire_headers_middleware_is_not_duplicated_on_update(): void
    {
        $beforeActionRoute = collect(Route::getRoutes()->getRoutes())->first(function ($route) {
            return $route->getName() === 'default-livewire.update';
        });

        $beforeActionCount = count(
            array_filter($beforeActionRoute->middleware(), fn ($m) => $m === RequireLivewireHeaders::class)
        );

        $this->assertEquals(1, $beforeActionCount);
        
        $testable = Livewire::test(new class extends TestComponent {});
        $encodedSnapshot = json_encode($testable->snapshot);

        // Livewire's update route should still be matched, not the catch-all
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => $encodedSnapshot, 'updates' => [], 'calls' => []],
            ]]);

        $response->assertOk();

        $afterActionRoute = collect(Route::getRoutes()->getRoutes())->first(function ($route) {
            return $route->getName() === 'default-livewire.update';
        });

        $afterActionCount = count(
            array_filter($afterActionRoute->middleware(), fn ($m) => $m === RequireLivewireHeaders::class)
        );

        $this->assertEquals(1, $afterActionCount);
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

    public function test_update_endpoint_accepts_a_gzipped_json_request_body(): void
    {
        if (! function_exists('gzencode')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        $testable = Livewire::test(new class extends TestComponent {
            public int $count = 0;

            public function increment(): void
            {
                $this->count++;
            }

            public function render()
            {
                return '<div>Count: {{ $count }}</div>';
            }
        });
        $body = json_encode([
            'components' => [[
                'snapshot' => json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [[
                    'method' => 'increment',
                    'params' => [],
                    'metadata' => [],
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'default-livewire.update');

        GzipPayloadProbe::$snapshot = null;
        GlobalGzipPayloadProbe::$snapshot = null;
        app(HttpKernel::class)->pushMiddleware(GlobalGzipPayloadProbe::class);
        $route->middleware(GzipPayloadProbe::class);
        $route->computedMiddleware = null;

        $response = $this->call(
            'POST',
            EndpointResolver::updatePath(),
            [],
            [],
            [],
            $this->livewireJsonServerHeaders(['HTTP_CONTENT_ENCODING' => 'gzip']),
            gzencode($body, 1),
        );

        $response->assertOk();
        $this->assertSame(
            json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
            GzipPayloadProbe::$snapshot,
        );
        $this->assertSame(
            json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
            GlobalGzipPayloadProbe::$snapshot,
        );
        $this->assertStringContainsString('Count: 1', $response->json('components.0.effects.html'));
    }

    public function test_gzip_decoder_is_the_first_global_middleware(): void
    {
        $middleware = new ReflectionProperty(
            \Illuminate\Foundation\Http\Kernel::class,
            'middleware',
        );

        $this->assertSame(
            DecodeGzipRequests::class,
            $middleware->getValue(app(HttpKernel::class))[0] ?? null,
        );
    }

    public function test_middleware_can_mutate_a_decoded_payload_before_execution(): void
    {
        if (! function_exists('gzencode')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        $testable = Livewire::test(new class extends TestComponent {
            public int $count = 0;

            public function original(): void
            {
                $this->count++;
            }

            public function mutated(): void
            {
                $this->count += 10;
            }

            public function render()
            {
                return '<div>Count: {{ $count }}</div>';
            }
        });
        $body = json_encode([
            'components' => [[
                'snapshot' => json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [[
                    'method' => 'original',
                    'params' => [],
                    'metadata' => [],
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'default-livewire.update');

        $route->middleware(GzipPayloadMutator::class);
        $route->computedMiddleware = null;

        $response = $this->call(
            'POST',
            EndpointResolver::updatePath(),
            [],
            [],
            [],
            $this->livewireJsonServerHeaders(['HTTP_CONTENT_ENCODING' => 'gzip']),
            gzencode($body, 1),
        );

        $response->assertOk();
        $this->assertStringContainsString('Count: 10', $response->json('components.0.effects.html'));
    }

    public function test_gzip_cannot_bypass_the_decompressed_payload_limit(): void
    {
        if (! function_exists('gzencode')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        config()->set('livewire.payload.max_size', 1024);

        $testable = Livewire::test(new class extends TestComponent {});
        $body = json_encode([
            'components' => [[
                'snapshot' => json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [[
                    'method' => 'unused',
                    'params' => [str_repeat('A', 10000)],
                    'metadata' => [],
                ]],
            ]],
        ], JSON_THROW_ON_ERROR);
        $compressed = gzencode($body, 1);

        $this->assertLessThan(1024, strlen($compressed));
        $this->assertGreaterThan(1024, strlen($body));
        $this->expectException(PayloadTooLargeException::class);

        $this->withoutExceptionHandling()->call(
            'POST',
            EndpointResolver::updatePath(),
            [],
            [],
            [],
            $this->livewireJsonServerHeaders(['HTTP_CONTENT_ENCODING' => 'gzip']),
            $compressed,
        );
    }

    public function test_update_endpoint_rejects_unknown_content_encoding(): void
    {
        $response = $this->call(
            'POST',
            EndpointResolver::updatePath(),
            [],
            [],
            [],
            $this->livewireJsonServerHeaders(['HTTP_CONTENT_ENCODING' => 'br']),
            '{"components":[]}',
        );

        $response->assertStatus(415);
    }

    public function test_delta_renderless_response_includes_the_top_level_transport_descriptor(): void
    {
        config()->set('livewire.update_engine', 'delta');
        config()->set('livewire.delta.request_compression', true);
        config()->set('livewire.delta.request_compression_minimum_bytes', 2048);

        $testable = Livewire::test(new class extends TestComponent {
            public function saveWithoutRendering(): void
            {
                $this->skipRender();
            }
        });

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'snapshot' => json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [[
                    'method' => 'saveWithoutRendering',
                    'params' => [],
                    'metadata' => [],
                ]],
            ]]]);

        $response->assertOk();
        $response->assertJsonPath('transport.v', 1);
        $response->assertJsonPath('transport.requestGzip', 2048);
        $this->assertArrayNotHasKey('html', $response->json('components.0.effects'));
    }

    public function test_update_endpoint_rejects_malformed_v1_render_metadata(): void
    {
        config()->set('app.debug', false);

        $testable = Livewire::test(new class extends TestComponent {});
        $snapshot = json_encode($testable->snapshot, JSON_THROW_ON_ERROR);
        $invalidMetadata = [
            'non-array' => 'bad',
            'unknown version' => ['v' => 2, 'capabilities' => []],
            'unknown capability' => ['v' => 1, 'capabilities' => ['execute-code']],
            'malformed base hash' => [
                'v' => 1,
                'capabilities' => ['same'],
                'base' => ['hash' => 'not-a-hash', 'bytes' => 1, 'revision' => 1],
            ],
            'negative base bytes' => [
                'v' => 1,
                'capabilities' => ['same'],
                'base' => ['hash' => str_repeat('a', 64), 'bytes' => -1, 'revision' => 1],
            ],
            'unsafe chunk size' => [
                'v' => 1,
                'capabilities' => ['chunks'],
                'chunks' => ['blockSize' => 64, 'blocks' => ''],
            ],
            'malformed fragment token' => [
                'v' => 1,
                'capabilities' => ['fragments'],
                'fragments' => [
                    'root' => str_repeat('a', 16),
                    'nodes' => [['unsafe-token', str_repeat('b', 16), str_repeat('c', 16)]],
                ],
            ],
        ];

        foreach ($invalidMetadata as $name => $render) {
            $response = $this->withHeaders(['X-Livewire' => 'true'])
                ->postJson(EndpointResolver::updatePath(), ['components' => [[
                    'snapshot' => $snapshot,
                    'updates' => [],
                    'calls' => [],
                    'render' => $render,
                ]]]);

            $this->assertSame(404, $response->status(), $name);
        }
    }

    public function test_protocol_safe_manifests_are_accepted_above_custom_candidate_limits(): void
    {
        config()->set('app.debug', false);
        config()->set('livewire.delta.maximum_manifest_bytes', 1);
        config()->set('livewire.delta.maximum_fragments', 0);

        $testable = Livewire::test(new class extends TestComponent {});
        $snapshot = json_encode($testable->snapshot, JSON_THROW_ON_ERROR);
        $render = [
            'v' => 1,
            'capabilities' => ['chunks', 'fragments'],
            'chunks' => [
                'blockSize' => 256,
                'blocks' => 'AAAA',
            ],
            'fragments' => [
                'root' => str_repeat('a', 16),
                'nodes' => [[
                    str_repeat('b', 16),
                    str_repeat('c', 16),
                    str_repeat('d', 16),
                ]],
            ],
        ];

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [],
                'render' => $render,
            ]]]);

        $response->assertOk();
    }

    public function test_snapshot_reference_is_resolved_before_component_actions_run(): void
    {
        config()->set('livewire.delta.snapshot_store', 'array');

        $testable = Livewire::test(new class extends TestComponent {
            public int $count = 0;

            public function increment(): void
            {
                $this->count++;
            }

            public function render()
            {
                return '<div>Count: {{ $count }}</div>';
            }
        });
        $snapshot = json_encode($testable->snapshot, JSON_THROW_ON_ERROR);
        $componentId = $testable->snapshot['memo']['id'];
        $reference = app(SnapshotStateStore::class)->put($componentId, $snapshot);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'id' => $componentId,
                'snapshotRef' => $reference,
                'updates' => [],
                'calls' => [[
                    'method' => 'increment',
                    'params' => [],
                    'metadata' => [],
                ]],
            ]]]);

        $response->assertOk();
        $this->assertStringContainsString('Count: 1', $response->json('components.0.effects.html'));
    }

    public function test_missing_snapshot_reference_returns_a_retryable_conflict(): void
    {
        config()->set('livewire.delta.snapshot_store', 'array');

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'id' => 'missing-component',
                'snapshotRef' => str_repeat('a', 24),
                'updates' => [],
                'calls' => [[
                    'method' => 'must-not-run',
                    'params' => [],
                    'metadata' => [],
                ]],
            ]]]);

        $response->assertStatus(409);
        $response->assertHeader('X-Livewire-Snapshot-Missing', '1');
        $response->assertJson(['snapshotMissing' => ['missing-component']]);
    }

    public function test_one_missing_snapshot_reference_prevents_every_action_in_the_batch(): void
    {
        config()->set('livewire.delta.snapshot_store', 'array');

        $component = new class extends TestComponent {
            public static int $runs = 0;

            public function run(): void
            {
                static::$runs++;
            }
        };
        $testable = Livewire::test($component);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                [
                    'snapshot' => json_encode($testable->snapshot, JSON_THROW_ON_ERROR),
                    'updates' => [],
                    'calls' => [[
                        'method' => 'run',
                        'params' => [],
                        'metadata' => [],
                    ]],
                ],
                [
                    'id' => 'missing-component',
                    'snapshotRef' => str_repeat('b', 24),
                    'updates' => [],
                    'calls' => [],
                ],
            ]]);

        $response->assertStatus(409);
        $this->assertSame(0, $component::$runs);
    }

    public function test_tampered_snapshot_reference_is_treated_as_a_cache_miss(): void
    {
        config()->set('livewire.delta.snapshot_store', 'array');

        $testable = Livewire::test(new class extends TestComponent {});
        $snapshot = json_encode($testable->snapshot, JSON_THROW_ON_ERROR);
        $componentId = $testable->snapshot['memo']['id'];
        $reference = app(SnapshotStateStore::class)->put($componentId, $snapshot);

        Cache::store('array')->put('livewire:snapshot:'.$reference, [
            'id' => $componentId,
            'hash' => hash('sha256', $snapshot),
            'snapshot' => $snapshot.'tampered',
        ], 300);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [[
                'id' => $componentId,
                'snapshotRef' => $reference,
                'updates' => [],
                'calls' => [],
            ]]]);

        $response->assertStatus(409);
        $response->assertJson(['snapshotMissing' => [$componentId]]);
    }

    protected function livewireJsonServerHeaders(array $headers = []): array
    {
        return $headers + [
            'HTTP_X_LIVEWIRE' => 'true',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];
    }
}

class GzipPayloadProbe
{
    public static ?string $snapshot = null;

    public function handle($request, \Closure $next)
    {
        static::$snapshot = $request->input('components.0.snapshot');

        return $next($request);
    }
}

class GlobalGzipPayloadProbe
{
    public static ?string $snapshot = null;

    public function handle($request, \Closure $next)
    {
        static::$snapshot = $request->input('components.0.snapshot');

        return $next($request);
    }
}

class GzipPayloadMutator
{
    public function handle($request, \Closure $next)
    {
        $components = $request->input('components');
        $components[0]['calls'][0]['method'] = 'mutated';
        $request->merge(['components' => $components]);

        return $next($request);
    }
}
