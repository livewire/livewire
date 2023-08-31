<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class CustomUpdateRouteTest extends \Tests\TestCase
{
    /** @test */
    public function can_customize_the_update_route_livewire_uses()
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle);
        });

        Livewire::test(ComponentForUpdateRouteCustomization::class)
            ->assertSet('count', 0)
            ->call('increment')
            ->assertSet('count', 1)
        ;
    }

    /** @test */
    public function can_use_a_custom_update_route_with_a_uri_segment()
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/{custom}/livewire/update', $handle);
        });

        Livewire::test(ComponentForUpdateRouteCustomization::class)
            ->assertSet('count', 0)
            ->call('increment')
            ->assertSet('count', 1)
        ;
    }
}

class ComponentForUpdateRouteCustomization extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return '<div></div>';
    }
}
