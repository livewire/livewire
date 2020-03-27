<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class RouteRegistrationTest extends TestCase
{
    /** @test */
    public function can_use_class_name_instead_of_component_name()
    {
        Livewire::component('foo', ComponentForRouteRegistration::class);

        Route::livewire('/foo', ComponentForRouteRegistration::class);

        $this->withoutExceptionHandling()
            ->get('/foo')
            ->assertSee('bar');
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
    public $name = 'bar';

    public function render()
    {
        return view('show-name');
    }
}
