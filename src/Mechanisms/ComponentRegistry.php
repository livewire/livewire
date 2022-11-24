<?php

namespace Livewire\Mechanisms;

use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Component;

class ComponentRegistry
{
    protected $nonAliasedClasses = [];
    protected $aliases = [];

    function boot()
    {
        app()->singleton($this::class);
    }

    function register($name, $class = null)
    {
        if (is_null($class)) {
            $this->nonAliasedClasses[] = $name;
        } else {
            $this->aliases[$name] = $class;
        }
    }

    function new($nameOrClass, $params = [], $id = null)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClass);

        $component = new $class;

        $component->setId($id ?: str()->random(20));

        $component->setName($name);

        // Parameters passed in automatically set public properties by the same name...
        foreach ($params as $key => $value) {
            if (! property_exists($component, $key)) continue;

            // Typed properties shouldn't be set back to "null". It will throw an error...
            if ((new \ReflectionProperty($component, $key))->getType() && is_null($value)) continue;

            $component->$key = $value;
        }

        return $component;
    }

    function getName($nameOrClassOrComponent)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClassOrComponent);

        return $name;
    }

    protected function getNameAndClass($nameComponentOrClass)
    {
        // If a component itself was passed in, just take the class name...
        $nameOrClass = is_object($nameComponentOrClass) ? $nameComponentOrClass::class : $nameComponentOrClass;

        // If a component class was passed in, use that...
        if (class_exists($nameOrClass)) {
            $class = $nameOrClass;
        // Otherwise, assume it was a simple name...
        } else {
            $class = $this->nameToClass($nameOrClass);
        }

        // Now that we have a class, we can check that it's actually a Livewire component...
        if (! is_subclass_of($class, Component::class)) {
            throw new ComponentNotFoundException(
                "Unable to find component: [{$nameOrClass}]"
            );
        }

        // Convert it to a name even if a name was passed in to make sure we're using deterministic names...
        $name = $this->classToName($class);

        return [$class, $name];
    }

    protected function nameToClass($name)
    {
        // Check the aliases...
        if (isset($this->aliases[$name])) return $this->aliases[$name];

        // Hash check the non-aliased classes...
        foreach ($this->nonAliasedClasses as $class) {
            if (crc32($class) === $name) {
                return $class;
            }
        }

        // Reverse generate a class from a name...
        return $this->generateClassFromName($name);
    }

    protected function classToName($class)
    {
        // Check the aliases...
        if ($name = array_search($class, $this->aliases)) return $name;

        // Check existance in non-aliased classes and hash...
        foreach ($this->nonAliasedClasses as $oneOff) {
            if (crc32($oneOff) === $hash = crc32($class)) {
                return $hash;
            }
        }

        // Generate name from class...
        return $this->generateNameFromClass($class);
    }

    protected function generateClassFromName($name)
    {
        $rootNamespace = config('livewire.class_namespace');

        $class = collect(str($name)->explode('.'))
            ->map(fn ($segment) => (string) str($segment)->studly())
            ->join('\\');

        return '\\' . $rootNamespace . '\\' . $class;
    }

    protected function generateNameFromClass($class)
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace'))))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.');

        $fullName = collect(explode('.', str_replace(['/', '\\'], '.', $class)))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.');

        if(str($fullName)->startsWith('.')) {
            $fullName = (string) str($fullName)->substr(1);
        }

        if (str($fullName)->startsWith($namespace)) {
            return (string) str($fullName)->substr(strlen($namespace) + 1);
        }

        return $fullName;
    }
}
