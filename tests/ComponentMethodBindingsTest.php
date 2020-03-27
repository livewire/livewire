<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class ComponentMethodBindingsTest extends TestCase
{
    /** @test */
    public function mount_method_receives_route_model_bindings()
    {
        Livewire::component('foo', ComponentWithBindings::class);

        Route::bind('foo', function ($value) {
            return new ModelToBeBoundStub($value);
        });

        Route::livewire('/test/{foo}', 'foo');

        $this->withoutExceptionHandling()->get('/test/from-injection')->assertSee('from-injection');
    }

    /** @test */
    public function mount_method_receives_bindings()
    {
        Livewire::test(ComponentWithBindings::class)
            ->assertSee('from-injection');
    }

    /** @test */
    public function mount_method_receives_bindings_with_subsequent_param()
    {
        Livewire::test(ComponentWithBindings::class, ['param' => 'foo'])
            ->assertSee('from-injectionfoo');
    }
}

class ModelToBeBoundStub
{
    public function __construct($value = 'from-injection')
    {
        $this->value = $value;
    }
}

class ComponentWithBindings extends Component
{
    public $name;

    public function mount(ModelToBeBoundStub $stub, $param = '')
    {
        $this->name = $stub->value.$param;
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}
