<?php

namespace Livewire;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionObject;

class AttributeCollection extends Collection
{
    protected $target;

    static function fromObject($target)
    {
        $instance = new static;

        $instance->setTarget($target);

        return $instance;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function find($class)
    {
        $attribute = $this->getAttributesByClass($class)[0] ?? null;

        if ($attribute) {
            return $attribute->newInstance();
        }
    }

    protected function getAttributesByClass($class)
    {
        $attributes = [];

        $reflected = new ReflectionObject($this->target);

        foreach ($reflected->getAttributes($class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
            $attributes[] = $attr;
        }

        foreach ($reflected->getMethods() as $method) {
            foreach ($method->getAttributes($class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                $attributes[] = $attr;
            }
        }

        foreach ($reflected->getProperties() as $property) {
            foreach ($property->getAttributes($class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                $attributes[] = $attr;
            }
        }

        return $attributes;
    }
}
