<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\View\Factory;
use Livewire\Livewire;
use Livewire\LivewireComponent;
use Livewire\LivewireComponentWrapper;
use Livewire\LivewireManager;

class CreatedMethodGetsRouteModelBindingsTest extends TestCase
{
    /** @test */
    function created_method_in_livewire_component_receives_route_model_bindings()
    {
        Route::bind('foo', function ($value) {
            return new ModelToBeBoundStub($value);
        });

        Route::livewire('/test/{foo}', HasCreatedMethodWithBindingExpectationStub::class);

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

class HasCreatedMethodWithBindingExpectationStub extends LivewireComponent {
    public function created(ModelToBeBoundStub $stub)
    {
        $this->value = $stub->value;
    }

    public function render()
    {
        return app('view')->make('public-properties-test')->with('name', $this->value);
    }
}
