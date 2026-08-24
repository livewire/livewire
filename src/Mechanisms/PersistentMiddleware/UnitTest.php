<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Livewire\Component as BaseComponent;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Sushi\Sushi;

class UnitTest extends \LegacyTests\Unit\TestCase
{
    public function test_it_does_not_have_persistent_middleware_memory_leak_when_adding_middleware()
    {
        $base = Livewire::getPersistentMiddleware();
        Livewire::addPersistentMiddleware('MyMiddleware');

        $config = $this->app['config'];
        $this->app->forgetInstances();
        $this->app->forgetScopedInstances();
        Facade::clearResolvedInstances();
        // Need to rebind these for the testcase cleanup to work.
        $this->app->instance('app', $this->app);
        $this->app->instance('config', $config);

        // It hangs around because it is a static variable, so we do expect
        // it to still exist here.
        $this->assertSame([
            ...$base,
            'MyMiddleware',
        ], Livewire::getPersistentMiddleware());

        Livewire::addPersistentMiddleware('MyMiddleware');
        $this->assertSame([
            ...$base,
            'MyMiddleware',
        ], Livewire::getPersistentMiddleware());
    }

    public function test_get_route_from_request_sets_container_on_matched_route()
    {
        Route::get('/test-container-path', fn() => 'test');

        $request = Request::create('/test-container-path', 'GET');

        $mechanism = app(PersistentMiddleware::class);

        $method = new \ReflectionMethod($mechanism, 'getRouteFromRequest');
        $method->setAccessible(true);

        $route = $method->invoke($mechanism, $request);

        $this->assertNotNull($route);

        $containerProp = new \ReflectionProperty($route, 'container');
        $containerProp->setAccessible(true);

        $this->assertSame(app(), $containerProp->getValue($route));
    }

    public function test_livewire_update_succeeds_when_view_route_has_stale_container()
    {
        $component = Livewire::test(EmptyComponent::class);
        $snapshot = json_encode($component->snapshot);

        // Replace the Livewire test route with a Route::view() route at the same
        // URI. Route::view() uses ViewController which requires ResponseFactory
        // from the route's container — the real-world trigger for this bug.
        $existingRoutes = app('router')->getRoutes();
        $newCollection = new RouteCollection;

        foreach ($existingRoutes as $route) {
            if (! str_contains($route->uri(), 'livewire-unit-test-endpoint')) {
                $newCollection->add($route);
                continue;
            }

            $viewRoute = new \Illuminate\Routing\Route(
                ['GET', 'HEAD'],
                $route->uri(),
                [
                    'uses'    => \Illuminate\Routing\ViewController::class.'@__invoke',
                    'view'    => 'test',
                    'data'    => [],
                    'status'  => 200,
                    'headers' => [],
                ]
            );

            $newCollection->add($viewRoute);
        }

        // setRoutes() calls $route->setContainer($this->container) on every route,
        // so we corrupt the container *after* to accurately simulate Octane's stale
        // sandbox scenario — where a previous request's flushed container is still
        // referenced by the route. Without the fix, ViewController cannot resolve
        // ResponseFactory from this empty container and throws BindingResolutionException.
        app('router')->setRoutes($newCollection);

        foreach (app('router')->getRoutes() as $route) {
            if (str_contains($route->uri(), 'livewire-unit-test-endpoint')) {
                $route->setContainer(new \Illuminate\Container\Container);
                break;
            }
        }

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), [
                'components' => [[
                    'calls'    => [],
                    'updates'  => [],
                    'snapshot' => $snapshot,
                ]],
            ]);

        $response->assertStatus(200);
    }

    public function test_it_resolves_empty_middleware_list_for_non_matching_routes()
    {
        $component = Livewire::test(EmptyComponent::class);
        $snapshot = json_encode($component->snapshot);

        // Remove the livewire testing route
        // This should cause the persistent middleware to fail resolving the route
        $existingRoutes = app('router')->getRoutes();
        $runningCollection = new RouteCollection;
        foreach ($existingRoutes as $route) {
            if (str_contains($route->uri, 'livewire-unit-test-endpoint')) {
                continue;
            }
            $runningCollection->add($route);
        }
        app('router')->setRoutes($runningCollection);

        // Hit update endpoint, including PersistentMiddleware
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), [
                'components' => [
                    [
                        'calls' => [],
                        'updates' => [],
                        'snapshot' => $snapshot
                    ]
                ]
            ]);
        $response->assertStatus(200);
        $response->assertJsonPath('components.0.snapshot', $snapshot);
    }

    public function test_it_does_not_fail_update_when_original_route_model_binding_is_missing()
    {
        // Route exists; model key does not → SubstituteBindings throws ModelNotFoundException
        Route::get('/posts/{post}', function (BindingPost $post) {
            return 'ok';
        })->middleware('web');

        $component = Livewire::test(EmptyComponent::class);
        $snapshot = $component->snapshot;

        // Point the memo at a non-existent model on a matching route
        $snapshot['memo']['path'] = 'posts/999999';
        $snapshot['memo']['method'] = 'GET';

        // Mutating memo will trigger CorruptComponentPayloadException -> 419
        // Unset original checksum and generate a new one
        unset($snapshot['checksum']);
        $snapshot['checksum'] = Checksum::generate($snapshot);

        $snapshotJson = json_encode($snapshot);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), [
                'components' => [[
                    'calls'    => [],
                    'updates'  => [],
                    'snapshot' => $snapshotJson,
                ]],
            ]);

        // Update endpoint must stay healthy; page-level 404 must not leak
        $response->assertStatus(200);
        $response->assertJsonPath('components.0.snapshot', $snapshotJson);
    }
}

class BindingPost extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'Exists'],
    ];
}

class EmptyComponent extends BaseComponent
{
    public function render()
    {
        return <<<'HTML'
        <div>

        </div>
        HTML;
    }
}
