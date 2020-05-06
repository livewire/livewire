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
    public function by_default_livewire_component_view_data_is_not_shared_with_outer_view_scope()
    {
        $this->expectErrorMessage('Undefined variable: title');

        Livewire::component('foo', ComponentWithTitleViewDataShared::class);

        Route::livewire('/foo', 'foo')
            ->layout('layouts.app-with-title');

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_share_livewire_component_view_data_with_outer_view_scope()
    {
        Livewire::component('foo', ComponentWithTitleViewDataShared::class);

        Livewire::routesShareComponentViewData();

        Route::livewire('/foo', 'foo')
            ->layout('layouts.app-with-title');

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
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

class ComponentWithTitleViewDataShared extends Component
{
    public function render()
    {
        return view('null-view', [
            'title' => 'baz',
        ]);
    }
}
