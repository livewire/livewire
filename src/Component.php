<?php

namespace Livewire;

use Livewire\Exceptions\PropertyNotFoundException;
use Illuminate\Support\Str;

abstract class Component extends \Synthetic\Component
{
    public function __invoke()
    {
        $finish = app('synthetic')->trigger('__invoke', $this);

        return $finish();
    }

    protected $__id;

    public function setId($id)
    {
        $this->__id = $id;
    }

    public function getId()
    {
        return $this->__id;
    }

    public static function getName()
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

    // [key => ['id' => id, 'tag' => tag]
    public $__children = [];
    public $__previous_children = [];

    public function getChildren() { return $this->__children; }

    public function setChildren($children) { $this->__children = $children; }

    public function setPreviouslyRenderedChildren($children) { $this->__previous_children = $children; }

    public function setChild($key, $tag, $id) { $this->__children[$key] = [$tag, $id]; }

    public function hasPreviouslyRenderedChild($key) {
        return in_array($key, array_keys($this->__previous_children));
    }

    public function hasChild($key)
    {
        return in_array($key, array_keys($this->__children));
    }

    public function getChild($key)
    {
        return $this->__children[$key];
    }

    public function getPreviouslyRenderedChild($key)
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

    public function __get($property)
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
}
