<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpLanguageLevelInspection */

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator;
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
        $this->value = $value;
        return $this;
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return new PropBoundModel($value);
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
