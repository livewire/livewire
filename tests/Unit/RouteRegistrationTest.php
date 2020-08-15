<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class RouteRegistrationTest extends TestCase
{
    /** @test */
    public function can_pass_parameters_to_a_layout_file()
    {
        Livewire::component(ComponentForRouteRegistration::class);

        Route::get('/foo', ComponentForRouteRegistration::class);

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }
}

class ComponentForRouteRegistration extends Component
{
    public $name = 'bar';

    public function render()
    {
        return view('show-name')->extends('layouts.app-with-bar', [
            'bar' => 'baz',
        ]);
    }
}
