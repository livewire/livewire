<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Tests\TestCase;

class CustomUpdateRouteUnitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            CustomUpdateRouteServiceProvider::class,
        ];
    }

    public function test_only_one_livewire_update_route_is_registered_with_custom_update_routes(): void
    {
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });

        $this->assertCount(1, $livewireUpdateRoutes);
        $this->assertEquals('custom/livewire/update', $livewireUpdateRoutes->first()->uri());
    }
}

// Service provider that sets custom route during boot (after Livewire's boot)
class CustomUpdateRouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle)->middleware('web');
        });
    }
}