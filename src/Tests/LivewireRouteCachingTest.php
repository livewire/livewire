<?php

namespace Livewire\Tests;

use Error;
use Exception;
use Illuminate\Routing\Route;
use Laravel\SerializableClosure\SerializableClosure;
use Tests\TestCase;

class LivewireRouteCachingTest extends TestCase
{
    /** @test */
    public function livewire_script_route_is_cacheable(): void
    {
        $route = $this->getRoute('livewire/livewire.js');

        $this->cacheRoute($route, "Failed to cache route 'livewire/livewire.js'");
    }

    /** @test */
    public function livewire_update_route_is_cacheable(): void
    {
        $route = $this->getRoute('livewire/update');

        $this->cacheRoute($route, "Failed to cache route 'livewire/update'");
    }

    protected function getRoute(string $uri): Route
    {
        $route = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->firstWhere(fn(Route $route) => $route->uri() === $uri);

        if ($route === null) {
            $this->fail("Route '$uri' not found.");
        }

        return $route;
    }

    protected function cacheRoute(Route $route, string $message): void
    {
        try {
            $route->prepareForSerialization();

            $this->assertStringContainsString(SerializableClosure::class, $route->getAction('uses'));
        } catch (Error|Exception) {
            $this->fail($message);
        }

        $this->assertTrue(true);
    }
}
