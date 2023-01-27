<?php

namespace Livewire\Features\SupportModels\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentTypedPropertyBindingsTest extends \Tests\TestCase
{
    /** @test */
    public function props_are_set_via_mount()
    {
        Livewire::test(ComponentWithPropBindings::class, [
            'model' => new PropBoundModel('mount-model'),
        ])->assertSeeText('prop:mount-model');
    }

    /** @test */
    public function props_are_set_via_implicit_binding()
    {
        Route::get('/foo/{model}', ComponentWithPropBindings::class);

        $this->get('/foo/route-model')->assertSeeText('prop:via-route:route-model');
    }

    /** @test */
    public function dependent_props_are_set_via_implicit_binding()
    {
        Route::get('/foo/{parent:custom}/bar/{child:custom}', ComponentWithDependentPropBindings::class);

        $this->get('/foo/robert/bar/bobby')->assertSeeText('prop:via-route:robert:via-parent:bobby');
    }

    /** @test */
    public function dependent_props_are_set_via_mount()
    {
        Route::get('/foo/{parent:custom}/bar/{child:custom}', ComponentWithDependentMountBindings::class);

        $this->get('/foo/robert/bar/bobby')->assertSeeText('prop:via-route:robert:via-parent:bobby');
    }

    /** @test */
    public function props_and_mount_work_together()
    {
        Route::get('/foo/{parent}/child/{child}', ComponentWithPropBindingsAndMountMethod::class);

        // In the case that a parent is a public property, and a child is injected via mount(),
        // the result will *not* resolve via the relationship (it's super edge-case and makes everything terrible)
        $this->withoutExceptionHandling()->get('/foo/parent-model/child/child-model')->assertSeeText('via-route:parent-model:via-route:child-model');
    }
}

class PropBoundModel extends Model
{
    public $value;

    public function __construct($value = 'model-default')
    {
        $this->value = $value;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $this->value = "via-route:$value";
        return $this;
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return new static("via-parent:$value");
    }
}

class ComponentWithPropBindings extends Component
{
    public PropBoundModel $model;

    public $name;

    public function render()
    {
        $this->name = 'prop:'.$this->model->value;

        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithDependentPropBindings extends Component
{
    public PropBoundModel $parent;

    public PropBoundModel $child;

    public $name;

    public function render()
    {
        $this->name = collect(['prop', $this->parent->value, $this->child->value])->implode(':');

        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithPropBindingsAndMountMethod extends Component
{
    public PropBoundModel $child;

    public $parent;

    public $name;

    public function mount(PropBoundModel $parent)
    {
        $this->parent = $parent;
    }

    public function render()
    {
        $this->name = "{$this->parent->value}:{$this->child->value}";

        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithDependentMountBindings extends Component
{
    public $parent;
    public $child;
    public $name;

    public function mount(PropBoundModel $parent, PropBoundModel $child)
    {
        $this->parent = $parent;
        $this->child = $child;
    }

    public function render()
    {
        $this->name = collect(['prop', $this->parent->value, $this->child->value])->implode(':');

        return app('view')->make('show-name-with-this');
    }
}
