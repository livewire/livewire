<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;
use Tests\TestComponent;

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
        // Both default and custom routes exist (with different names to avoid collision)
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });

        $this->assertCount(2, $livewireUpdateRoutes);
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
        $testable = Livewire::test(new class extends TestComponent {});
        $snapshotJson = json_encode($testable->snapshot);

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson('/custom/livewire/update', ['components' => [
                ['snapshot' => $snapshotJson, 'updates' => [], 'calls' => []],
            ]]);

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
