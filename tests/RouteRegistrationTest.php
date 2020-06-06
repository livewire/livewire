<?php

namespace Tests;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class RouteRegistrationTest extends TestCase
{
    /** @test */
    public function can_register_route_with_component_mount_as_the_route_action()
    {
        Livewire::component('foo', ComponentForRouteRegistration::class);

        Route::livewire('/foo', 'foo');

        $this->get('/foo')->assertSee('bar');
    }

    /** @test */
    public function can_register_mount_as_route_action_without_side_effects()
    {
        Livewire::component('foo', ComponentForRouteRegistration::class);

        Route::livewire('/foo', 'foo');

        // request the route to trigger the registered callback
        $this->get('/foo');

        try {
            // check for side effects - copy routes to new collection
            // as Livewire::test() does to insert test route
            $routes = new RouteCollection;
            foreach (app('router')->getRoutes() as $route) {
                $routes->add($route);
            }
        } catch (\Exception $e) {
            $this->fail(sprintf(
                "Failed to register Livewire route without side effect ['%s']",
                $e->getMessage()
            ));
        }

        $this->assertTrue(true);
    }

    /** @test */
    public function can_pass_parameters_to_a_layout_file()
    {
        Livewire::component('foo', ComponentForRouteRegistration::class);

        Route::livewire('/foo', 'foo')->layout('layouts.app-with-bar', [
            'bar' => 'baz',
        ]);

        $this->get('/foo')->assertSee('baz');
    }
}

class ComponentForRouteRegistration extends Component
{
    public $name = '';

    public function mount()
    {
        $this->name = 'bar';
    }

    public function render()
    {
        return view('show-name');
    }
}
