<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class EnforceWebMiddlewareUnitTest extends TestCase
{
    public function test_web_middleware_is_automatically_added_to_custom_route_when_missing(): void
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle);
        });

        $route = $this->findCustomUpdateRoute();

        $this->assertNotNull($route, 'Custom livewire.update route should exist');
        $this->assertContains('web', $route->middleware());
    }

    public function test_web_middleware_is_not_duplicated_when_already_present(): void
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle)->middleware('web');
        });

        $route = $this->findCustomUpdateRoute();

        $middlewareCount = count(array_filter($route->middleware(), fn ($m) => $m === 'web'));

        $this->assertEquals(1, $middlewareCount);
    }

    public function test_web_middleware_is_detected_from_route_group(): void
    {
        Route::middleware('web')->group(function () {
            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/custom/livewire/update', $handle);
            });
        });

        $route = $this->findCustomUpdateRoute();

        $middlewareCount = count(array_filter($route->middleware(), fn ($m) => $m === 'web'));

        $this->assertEquals(1, $middlewareCount);
    }

    public function test_additional_middleware_is_preserved_when_web_is_auto_added(): void
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle)->middleware('auth');
        });

        $route = $this->findCustomUpdateRoute();

        $this->assertNotNull($route, 'Custom livewire.update route should exist');

        $middleware = $route->middleware();

        $this->assertContains('web', $middleware);
        $this->assertContains('auth', $middleware);
    }

    protected function findCustomUpdateRoute()
    {
        return collect(Route::getRoutes()->getRoutes())->first(function ($route) {
            return str($route->getName())->endsWith('livewire.update')
                && $route->getName() !== 'default-livewire.update';
        });
    }
}
