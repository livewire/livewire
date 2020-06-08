<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class RouteRegistrationTest extends TestCase
{
    /** @test */
    public function can_pass_parameters_to_a_layout_file()
    {
        Livewire::component('foo', ComponentForRouteRegistration::class);

        Route::livewire('/foo', 'foo')->layout('layouts.app-with-bar', [
            'bar' => 'baz',
        ]);

        $this->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_specify_layout_and_section_in_render_method()
    {
        Livewire::component('foo', ComponentForRouteRegistrationWithExtends::class);

        Route::livewire('/foo', 'foo');

        $this->get('/foo')
            ->assertSee('bar')
            ->assertSee('baz');
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

class ComponentForRouteRegistrationWithExtends extends Component
{
    public $name = 'bar';

    public function render()
    {
        return view('show-name')
            ->extends('layouts.app-with-bar-and-yield-body', [
                'bar' => 'baz',
            ])->section('body');
    }
}
