<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentRouteBindingsTest extends TestCase
{
    /** @test */
    public function component_can_be_registered_as_route_target_without_side_effects()
    {
        Livewire::component('foo', ComponentWithoutBindings::class);

        Route::livewire('/foo', 'foo');

        $this->withoutExceptionHandling()
            ->get('/foo')
            ->assertSeeText('param-default');

        // check for side effects, which would appear with subsequent requests
        $this->withoutExceptionHandling()
            ->get('/foo')
            ->assertSeeText('param-default');
        Livewire::test(ComponentWithoutBindings::class)
            ->assertSeeText('param-default');
    }

    /** @test */
    public function mount_method_receives_explicit_bindings_registered_for_route()
    {
        Livewire::component('foo', ComponentWithClassBindings::class);

        Route::bind('foo', function ($value) {
            return new ClassToBeBound($value);
        });

        Route::livewire('/test/{foo}', 'foo');

        $this->withoutExceptionHandling()
            ->get('/test/from-injection')
            ->assertSeeText('from-injection')
            ->assertSeeText('param-default');
    }

    /** @test */
    public function mount_method_receives_implicit_route_model_bindings()
    {
        Livewire::component('foo', ComponentWithModelBindings::class);

        Route::livewire('/test/{foo}', 'foo');

        $this->withoutExceptionHandling()
            ->get('/test/from-injection')
            ->assertSeeText('from-injection')
            ->assertSeeText('param-default');
    }

    /** @test */
    public function mount_method_receives_implicit_route_model_relationship_bindings()
    {
        if (version_compare(Application::VERSION, '7.0', '<')) {
            $this->markTestSkipped('scoping of implicit route binding is unavailable prior to Laravel 7.0');
        }

        Livewire::component('foo', ComponentWithModelRelationshipBindings::class);

        Route::livewire('/test/{parent}/{child:id}', 'foo');

        $this->withoutExceptionHandling()
            ->get('/test/moms/first-born')
            ->assertSeeText('moms')
            ->assertSeeText('child')
            ->assertSeeText('first-born')
            ->assertSeeText('param-default');
    }

    /** @test */
    public function component_without_bindings_can_be_mounted_for_route_with_parameters()
    {
        Livewire::component('foo', ComponentWithoutBindings::class);

        Route::livewire('/test/{foo}', 'foo');

        $this->withoutExceptionHandling()
            ->get('/test/foo')
            ->assertDontSeeText('foo')
            ->assertSeeText('param-default');
    }

    /** @test */
    public function mount_method_can_simulate_route_bindings()
    {
        Livewire::test(ComponentWithModelBindings::class, [
            'foo' => (new RouteBindingsTestModel('from-injection'))
        ])->assertSeeText('from-injection')->assertSeeText('param-default');

        Livewire::test(ComponentWithClassBindings::class, [
            'foo' => (new ClassToBeBound('from-injection'))
        ])->assertSeeText('from-injection')->assertSeeText('param-default');

        Livewire::test(ComponentWithModelRelationshipBindings::class, [
            'parent' => (new RouteBindingsTestModel('moms')),
            'child' => (new RoutBindingsTestChildModel('first-born')),
        ])->assertSeeText('moms')->assertSeeText('first-born');
    }
}

class ClassToBeBound
{
    public function __construct($value = 'class-default')
    {
        $this->value = $value;
    }
}

class RouteBindingsTestModel extends Model
{
    public function __construct($value = 'model-default')
    {
        $this->value = $value;
    }
    public function resolveRouteBinding($value, $field = null)
    {
        $this->value = $value;
        return $this;
    }
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return new RoutBindingsTestChildModel($childType.':'.$value);
    }
}

class RoutBindingsTestChildModel extends RouteBindingsTestModel {}

class ComponentWithModelBindings extends Component
{
    public $name;

    public function mount(RouteBindingsTestModel $foo, $param = 'param-default')
    {
        $this->name = $foo->value.':'.$param;
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithClassBindings extends Component
{
    public $name;

    public function mount(ClassToBeBound $foo, $param = 'param-default')
    {
        $this->name = $foo->value.':'.$param;
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithModelRelationshipBindings extends Component
{
    public $name;

    public function mount(RouteBindingsTestModel $parent, $param = 'param-default', RoutBindingsTestChildModel $child)
    {
        $this->name = $parent->value.':'.$param.':'.$child->value;
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithoutBindings extends Component
{
    public $name;

    public function mount($param = 'param-default')
    {
        $this->name = $param;
    }

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}
