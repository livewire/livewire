<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Tests\TestCase;

class CustomUploadRouteUnitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            CustomUploadRouteServiceProvider::class,
        ];
    }

    public function test_only_one_livewire_upload_route_is_registered_with_custom_upload_routes(): void
    {
        $livewireUploadRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.upload-file');
        });

        $this->assertCount(1, $livewireUploadRoutes);
        $this->assertEquals('custom/livewire/upload-file', $livewireUploadRoutes->first()->uri());
    }

    public function test_only_one_livewire_preview_route_is_registered_with_custom_preview_routes(): void
    {
        $livewirePreviewRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.preview-file');
        });

        $this->assertCount(1, $livewirePreviewRoutes);
        $this->assertEquals('custom/livewire/preview-file', $livewirePreviewRoutes->first()->uri());
    }
}

// Service provider that sets custom routes during boot (after Livewire's boot)
class CustomUploadRouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::setUploadRoute(function ($handle) {
            return Route::post('/custom/livewire/upload-file', $handle)->middleware('web');
        });

        Livewire::setPreviewRoute(function ($handle) {
            return Route::get('/custom/livewire/preview-file', $handle)->middleware('web');
        });
    }
}
