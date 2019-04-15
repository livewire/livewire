<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Livewire\LivewireComponent;

class MountMethodGetsRouteModelBindingsTest extends TestCase
{
    /** @test */
    function mount_method_in_livewire_component_receives_route_model_bindings()
    {
        Route::bind('foo', function ($value) {
            return new ModelToBeBoundStub($value);
        });

        Route::livewire('/test/{foo}', HasMountMethodWithBindingExpectationStub::class);

        $response = $this->get('/test/render-in-view');

        $this->assertContains('render-in-view', $response->original->render());
    }
}

class ModelToBeBoundStub
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}

class HasMountMethodWithBindingExpectationStub extends LivewireComponent {
    public function mount(ModelToBeBoundStub $stub)
    {
        $this->value = $stub->value;
    }

    public function render()
    {
        return app('view')->make('show-name')->with('name', $this->value);
    }
}
