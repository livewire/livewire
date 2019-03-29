<?php

namespace Livewire;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

abstract class LivewireComponent
{
    use Concerns\CanBeSerialized,
        Concerns\ValidatesInput,
        Concerns\TracksDirtySyncedInputs,
        Concerns\HasLifecycleHooks,
        Concerns\RegistersListeners,
        Concerns\ReceivesEvents;

    public $id;
    public $prefix;
    public $redirectTo;

    public function __construct($id, $prefix)
    {
        $this->id = $id;
        $this->prefix = $prefix;
        $this->hashCurrentObjectPropertiesForEasilyDetectingChangesLater();
    }

    public function output($errors = null)
    {
        $view = $this->render();

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        return $view
            ->with([
                'errors' => (new ViewErrorBag)->put('default', $errors ?: new MessageBag),
            ])
            // Automatically inject all public properties into the blade view.
            ->with($this->getPublicPropertiesDefinedBySubClass())
            ->render();
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

    public function getAllPropertiesDefinedBySubClass()
    {
        $properties = (new \ReflectionClass($this))->getProperties();
        $data = [];

        foreach ($properties as $property) {
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
