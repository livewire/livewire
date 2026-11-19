<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Livewire\Component as BaseComponent;
use Livewire\Drawer\Utils;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;

class UnitTest extends \LegacyTests\Unit\TestCase
{
    public function test_persistent_middleware_response_is_returned_on_subsequent_requests()
    {
        Route::get('/with-response-middleware', fn () => 'page')
            ->middleware(ReturnsResponseMiddleware::class);

        Livewire::addPersistentMiddleware(ReturnsResponseMiddleware::class);

        $snapshot = Livewire::test(ComponentWithAction::class)->snapshot;
        $snapshot['memo']['path'] = 'with-response-middleware';
        $snapshot['memo']['method'] = 'GET';
        unset($snapshot['checksum']);
        $snapshot['checksum'] = Checksum::generate($snapshot);

        ComponentWithAction::$called = false;

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson('/livewire/update', [
                'components' => [[
                    'calls' => [['method' => 'save', 'params' => [], 'path' => '']],
                    'updates' => [],
                    'snapshot' => json_encode($snapshot),
                ]],
            ]);

        $response->assertStatus(418);
        $this->assertFalse(ComponentWithAction::$called);
    }

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

    public function test_it_rejects_updates_when_the_original_route_no_longer_matches()
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
        $response = $this->post('/livewire/update', [
            'components' => [
                [
                    'calls' => [],
                    'updates' => [],
                    'snapshot' => $snapshot
                ]
            ]
        ]);
        $response->assertStatus(404);
    }

    public function test_it_rejects_an_update_sent_to_an_unmatched_host()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        ProtectedActionMiddleware::$allowed = false;

        $this->updateComponent('app.example.test', $snapshot)->assertForbidden();
        $this->assertSame(0, ProtectedActionComponent::$calls);

        $this->updateComponent('example.test', $snapshot)->assertNotFound();
        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_rejects_an_update_when_an_unprotected_route_matches_on_the_update_host()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        Route::domain('example.test')->get('/protected', static fn () => 'Unprotected route');

        ProtectedActionMiddleware::$allowed = false;

        $this->updateComponent('example.test', $snapshot)->assertNotFound();
        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_rejects_an_update_on_another_host_that_matches_the_same_domain_route()
    {
        $snapshot = $this->renderProtectedComponent('acme.example.test', '{tenant}.example.test');

        $this->updateComponent('globex.example.test', $snapshot)->assertNotFound();
        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_rejects_an_update_when_the_original_route_is_replaced_by_a_fallback()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        $this->removeProtectedRoute();
        Route::fallback(static fn () => 'Fallback route')->domain('app.example.test');

        $this->updateComponent('app.example.test', $snapshot)->assertNotFound();
        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_returns_not_found_when_only_a_different_route_method_remains()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        $this->removeProtectedRoute();
        Route::domain('app.example.test')->post('/protected', static fn () => 'Different method');

        $this->updateComponent('app.example.test', $snapshot)->assertNotFound();
        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_rejects_a_legacy_snapshot_without_a_signed_origin_and_route()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        unset($snapshot['memo']['origin'], $snapshot['memo']['route'], $snapshot['checksum']);
        $snapshot['checksum'] = Checksum::generate($snapshot);

        $this->updateComponent('app.example.test', $snapshot)->assertNotFound();
        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_rejects_an_update_when_the_scheme_changes()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        $this->withHeaders(['X-Livewire' => 'true'])->postJson('https://app.example.test/livewire/update', [
            'components' => [[
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [['method' => 'write', 'path' => '', 'params' => []]],
            ]],
        ])->assertNotFound();

        $this->assertSame(0, ProtectedActionComponent::$calls);
    }

    public function test_it_allows_an_update_on_the_original_host_when_access_is_allowed()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        $this->updateComponent('app.example.test', $snapshot)->assertSuccessful();
        $this->assertSame(1, ProtectedActionComponent::$calls);
    }

    public function test_it_keeps_the_original_route_context_in_subsequent_snapshots()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        $response = $this->updateComponent('app.example.test', $snapshot)->assertSuccessful();
        $updatedSnapshot = json_decode($response->json('components.0.snapshot'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($snapshot['memo']['origin'], $updatedSnapshot['memo']['origin']);
        $this->assertSame($snapshot['memo']['route'], $updatedSnapshot['memo']['route']);

        $this->updateComponent('app.example.test', $updatedSnapshot)->assertSuccessful();
        $this->assertSame(2, ProtectedActionComponent::$calls);
    }

    public function test_it_allows_a_same_origin_custom_update_route()
    {
        $snapshot = $this->renderProtectedComponent('app.example.test');

        Livewire::setUpdateRoute(static fn ($handle) => Route::post('/custom/livewire/update', $handle));

        $this->withHeaders(['X-Livewire' => 'true'])->postJson('http://app.example.test/custom/livewire/update', [
            'components' => [[
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [['method' => 'write', 'path' => '', 'params' => []]],
            ]],
        ])->assertSuccessful();

        $this->assertSame(1, ProtectedActionComponent::$calls);
    }

    private function renderProtectedComponent(string $host, ?string $domain = null): array
    {
        ProtectedActionMiddleware::$allowed = true;
        ProtectedActionComponent::$calls = 0;

        Livewire::component('protected-action-component', ProtectedActionComponent::class);
        Livewire::addPersistentMiddleware(ProtectedActionMiddleware::class);

        Route::domain($domain ?? $host)
            ->middleware(ProtectedActionMiddleware::class)
            ->get('/protected', static fn () => Blade::render('<livewire:protected-action-component />'));

        $response = $this->get("http://{$host}/protected")->assertSuccessful();

        return Utils::extractAttributeDataFromHtml($response->getContent(), 'wire:snapshot');
    }

    private function updateComponent(string $host, array $snapshot)
    {
        return $this->withHeaders(['X-Livewire' => 'true'])->postJson("http://{$host}/livewire/update", [
            'components' => [[
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'updates' => [],
                'calls' => [['method' => 'write', 'path' => '', 'params' => []]],
            ]],
        ]);
    }

    private function removeProtectedRoute(): void
    {
        $remainingRoutes = new RouteCollection;

        foreach (app('router')->getRoutes() as $route) {
            if ($route->uri() === 'protected' && $route->getDomain() === 'app.example.test') {
                continue;
            }

            $remainingRoutes->add($route);
        }

        app('router')->setRoutes($remainingRoutes);
    }

}

class ComponentWithAction extends BaseComponent
{
    public static $called = false;

    public function save()
    {
        static::$called = true;
    }

    public function render()
    {
        return '<div></div>';
    }
}

class ReturnsResponseMiddleware
{
    public function handle($request, $next)
    {
        return response()->json([], 418);
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

class ProtectedActionMiddleware
{
    public static bool $allowed = true;

    public function handle(Request $request, Closure $next)
    {
        abort_unless(static::$allowed, 403);

        return $next($request);
    }
}

class ProtectedActionComponent extends BaseComponent
{
    public static int $calls = 0;

    public function write(): void
    {
        static::$calls++;
    }

    public function render(): string
    {
        return '<div>Ready</div>';
    }
}
