<?php

namespace Livewire\Features\SupportRouting;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function test_can_route_to_a_class_based_component_from_standard_route()
    {
        Route::get('/component-for-routing', ComponentForRouting::class);

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_can_use_livewire_macro_to_route_directory_to_class_based_components()
    {
        Route::livewire('/component-for-routing', ComponentForRouting::class);

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_can_use_livewire_macro_to_define_routes()
    {
        Livewire::component('component-for-routing', ComponentForRouting::class);

        Route::livewire('/component-for-routing', 'component-for-routing');

        $this
            ->withoutExceptionHandling()
            ->get('/component-for-routing')
            ->assertSee('Component for routing');
    }

    public function test_route_parameters_are_passed_to_component()
    {
        Route::livewire('/route-with-params/{myId}', ComponentForRoutingWithParams::class);

        $this->get('/route-with-params/123')->assertSeeText('123');
    }
}

class ComponentForRouting extends Component
{
    public function render()
    {
        return '<div>Component for routing</div>';
    }
}

class ComponentForRoutingWithParams extends Component
{
    public $myId;

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ $myId }}
        </div>
        HTML;
    }
}
