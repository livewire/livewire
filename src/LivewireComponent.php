<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

abstract class LivewireComponent
{
    use Concerns\CanBeSerialized,
        Concerns\ValidatesInput,
        Concerns\TracksDirtySyncedInputs,
        Concerns\RegistersListeners,
        Concerns\ReceivesEvents;

    public $id;
    public $prefix;
    public $redirectTo;

    protected $lifecycleHooks = [
        'created', 'updated', 'updating',
    ];

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

    public function getPropertyValue($prop)
    {
        // This is used by wrappers. Otherwise,
        // users would have to declare props as "public".
        return $this->{$prop};
    }

    public function hasProperty($prop)
    {
        return property_exists($this, $prop);
    }

    public function setPropertyValue($name, $value)
    {
        $hasArrayKey = count(explode('.', $name)) > 1;

        if ($hasArrayKey) {
            $keys = explode('.', $name);
            $firstKey = array_shift($keys);
            Arr::set($this->{$firstKey}, implode('.', $keys), $value);
        } else {
            return $this->{$name} = $value;
        }
    }

    public function __call($method, $params)
    {
        if (
            in_array($method, $this->lifecycleHooks)
            || Str::startsWith($method, ['updating', 'updated'])
        ) {
            return;
        }


        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
