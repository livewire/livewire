<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

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

#[\AllowDynamicProperties]
class ComponentWithPropBindingsAndMountMethod extends Component
{
    public PropBoundModel $child;

    public $parent;

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
