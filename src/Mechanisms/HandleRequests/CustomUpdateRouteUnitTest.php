<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
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

    public function test_custom_route_is_used_for_url_generation(): void
    {
        $this->assertEquals('/custom/livewire/update', Livewire::getUpdateUri());
    }

    public function test_default_route_returns_404_when_custom_route_registered(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->post(EndpointResolver::updatePath(), ['components' => []]);

        $response->assertNotFound();
    }

    public function test_custom_route_accepts_requests_when_registered(): void
    {
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->post('/custom/livewire/update', ['components' => []]);

        $response->assertOk();
        $this->assertArrayHasKey('components', $response->json());
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
