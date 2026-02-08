<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Facade;
use Livewire\Component as BaseComponent;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

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
        $response = $this->post(EndpointResolver::updatePath(), [
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
