<?php

namespace Livewire;

use Synthetic\Utils as SyntheticUtils;
use Livewire\Features\SupportValidation\HandlesValidation;
use Livewire\Features\SupportEvents\HandlesEvents;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Drawer\Utils;
use Livewire\Concerns\InteractsWithProperties;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Str;
use BadMethodCallException;

/**
 * @todo - add Facade-esque method signatures to this file (from triggered __get and __call)
 */

abstract class Component extends \Synthetic\Component
{
    use Macroable { __call as macroCall; }

    use InteractsWithProperties;
    use HandlesEvents;
    use HandlesValidation;

    function __invoke()
    {
        $finish = app('synthetic')->trigger('__invoke', $this);

        return $finish();
    }

    protected $__id;

    function setId($id)
    {
        $this->__id = $id;
    }

    function getId()
    {
        return $this->__id;
    }

    static function getName()
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace'))))
            ->map(fn ($i) => Str::kebab($i))
            ->implode('.');

        $fullName = collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->map(fn ($i) => Str::kebab($i))
            ->implode('.');

        if (str($fullName)->startsWith($namespace)) {
            return (string) str($fullName)->substr(strlen($namespace) + 1);
        }

        return $fullName;
    }

    /**
     * @todo: move all this children stuff to "single file"
     */
    // [key => ['id' => id, 'tag' => tag]
    public $__children = [];
    public $__previous_children = [];

    function getChildren() { return $this->__children; }

    function setChildren($children) { $this->__children = $children; }

    function setPreviouslyRenderedChildren($children) { $this->__previous_children = $children; }

    function setChild($key, $tag, $id) { $this->__children[$key] = [$tag, $id]; }

    function hasPreviouslyRenderedChild($key) {
        return in_array($key, array_keys($this->__previous_children));
    }

    function hasChild($key)
    {
        return in_array($key, array_keys($this->__children));
    }

    function getChild($key)
    {
        return $this->__children[$key];
    }

    function getPreviouslyRenderedChild($key)
    {
        return $this->__previous_children[$key];
    }

    function __isset($property)
    {
        try {
            $this->__get($property);

            return true;
        } catch(PropertyNotFoundException $e) {
            return false;
        }

        return false;
    }

    function __get($property)
    {
        $value = 'noneset';

        $returnValue = function ($newValue) use (&$value) {
            $value = $newValue;
        };

        $finish = app('synthetic')->trigger('__get', $this, $property, $returnValue);

        $value = $finish($value);

        if ($value === 'noneset') {
            throw new PropertyNotFoundException($property, $this->getName());
        }

        return $value;
    }

    function __call($method, $params)
    {
        $value = 'noneset';

        $returnValue = function ($newValue) use (&$value) {
            $value = $newValue;
        };

        $finish = app('synthetic')->trigger('__call', $this, $method, $params, $returnValue);

        $value = $finish($value);

        if ($value !== 'noneset') {
            return $value;
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
