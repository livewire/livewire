<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class MountMethodGetsRouteModelBindingsTest extends TestCase
{
    /** @test */
    function mount_method_in_livewire_component_receives_route_model_bindings()
    {
        Route::bind('foo', function ($value) {
            return new ModelToBeBoundStub($value);
        });

        app('livewire')->component('foo', HasMountMethodWithBindingExpectationStub::class);
        Route::livewire('/test/{foo}', 'foo');

        $response = $this->withoutExceptionHandling()->get('/test/render-in-view');

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

class HasMountMethodWithBindingExpectationStub extends Component {
    public function mount(ModelToBeBoundStub $stub)
    {
        $this->value = $stub->value;
    }

    public function render()
    {
        return app('view')->make('show-name')->with('name', $this->value);
    }
}
