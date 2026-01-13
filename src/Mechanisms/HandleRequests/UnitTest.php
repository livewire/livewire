<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_livewire_can_run_handle_request_without_components_on_payload(): void
    {
        $handleRequestsInstance = new HandleRequests();
        $request = new Request();

        $result = $handleRequestsInstance->handleUpdate($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('components', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertIsArray($result['components']);
        $this->assertEmpty($result['components']);
        $this->assertIsArray($result['assets']);
        $this->assertEmpty($result['assets']);
    }

    public function test_default_livewire_update_route_is_registered(): void
    {
        $livewireUpdateRoutes = collect(Route::getRoutes()->getRoutes())->filter(function ($route) {
            return str($route->getName())->endsWith('livewire.update');
        });

        $this->assertCount(1, $livewireUpdateRoutes);
        $this->assertEquals(ltrim(EndpointResolver::updatePath(), '/'), $livewireUpdateRoutes->first()->uri());
    }
}
