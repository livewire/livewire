<?php

namespace Livewire\Mechanisms\HandleRouting;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireServiceProvider;
use Tests\TestCase;

class ProviderOrderUnitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            RoutesBeforeLivewireServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    public function test_lazy_route_macros_are_available_to_packages_that_boot_before_livewire()
    {
        $this->assertTrue(Route::getRoutes()->getByName('package.lazy')->defaults['lazy']);
        $this->assertTrue(Route::getRoutes()->getByName('package.defer')->defaults['defer']);
    }
}

class RoutesBeforeLivewireServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::livewire('/package-lazy', 'package-lazy')->lazy()->name('package.lazy');
        Route::livewire('/package-defer', 'package-defer')->defer()->name('package.defer');
    }
}
