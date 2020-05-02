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

    /** @test */
    public function custom_method_recieves_bindings()
    {
        $component = Livewire::test(ComponentWithBindings::class);

        $component->call('customMethod', '/path/to/some/file');

        $this->assertEquals('/path/to/some/file', $component->path);
        $this->assertEquals('from-injection', $component->stubValue);
    }

    /** @test */
    public function custom_method_without_dependencies()
    {
        $component = Livewire::test(ComponentWithBindings::class);

        $component->call('customMethodWithoutDependencies');

        $this->assertEquals(null, $component->path);
        $this->assertEquals(null, $component->stubValue);
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

    public $path;

    public $stubValue;

    public function mount(ModelToBeBoundStub $stub, $param = '')
    {
        $this->name = $stub->value.$param;
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }

    public function customMethod($path, ModelToBeBoundStub $stub)
    {
        $this->path = $path;
        $this->stubValue = $stub->value;
    }

    public function customMethodWithoutDependencies()
    {
        
    }
}
