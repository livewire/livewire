<?php

namespace Livewire;

abstract class LivewireComponent
{
    use Concerns\CanBeSerialized,
        Concerns\ValidatesInput;

    public $id;
    public $prefix;
    public $redirectTo;

    public function __construct($id, $prefix)
    {
        $this->id = $id;
        $this->prefix = $prefix;
    }

    public function redirect($url)
    {
        $this->redirectTo = $url;
    }

    public function getPublicPropertiesDefinedBySubClass()
    {
        $publicProperties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = [];

        foreach ($publicProperties as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }

    public function getPropertyValue($prop) {
        // This is used by wrappers. Otherwise,
        // users would have to declare props as "public".
        return $this->{$prop};
    }

    public function hasProperty($prop) {
        return property_exists($this, $prop);
    }

    public function setPropertyValue($prop, $value) {
        return $this->{$prop} = $value;
    }
}
