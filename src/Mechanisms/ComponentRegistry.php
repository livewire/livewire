<?php

namespace Livewire\Mechanisms;

use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Component;

class ComponentRegistry extends Mechanism
{
    protected $missingComponentResolvers = [];
    protected $nonAliasedClasses = [];
    protected $aliases = [];

    function component($name, $class = null)
    {
        if (is_null($class)) {
            $this->nonAliasedClasses[] = $name;
        } else {
            $this->aliases[$name] = $class;
        }
    }

    function new($nameOrClass, $id = null)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClass);

        $component = new $class;

        $component->setId($id ?: str()->random(20));

        $component->setName($name);

        // // Parameters passed in automatically set public properties by the same name...
        // foreach ($params as $key => $value) {
        //     if (! property_exists($component, $key)) continue;

        //     // Typed properties shouldn't be set back to "null". It will throw an error...
        //     if ((new \ReflectionProperty($component, $key))->getType() && is_null($value)) continue;

        //     $component->$key = $value;
        // }

        return $component;
    }

    function isDiscoverable($classOrName)
    {
        if (is_object($classOrName)) {
            $classOrName = get_class($classOrName);
        }

        if (class_exists($name = $classOrName)) {
            $name = $this->generateNameFromClass($classOrName);
        }

        $class = $this->generateClassFromName($name);

        if (class_exists($class) && is_subclass_of($class, Component::class)) {
            return true;
        }

        return false;
    }

    function getName($nameOrClassOrComponent)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClassOrComponent);

        return $name;
    }

    function getClass($nameOrClassOrComponent)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClassOrComponent);

        return $class;
    }

    function resolveMissingComponent($resolver)
    {
        $this->missingComponentResolvers[] = $resolver;
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

            // If class can't be found, see if there is an index component in a subfolder...
            if(! class_exists($class)) {
                $class = $class . '\\Index';
            }

            if(! class_exists($class)) {
                foreach ($this->missingComponentResolvers as $resolve) {
                    if ($resolved = $resolve($nameOrClass)) {
                        $this->component($nameOrClass, $resolved);

                        $class = $this->aliases[$nameOrClass];

                        break;
                    }
                }
            }
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
        if (isset($this->aliases[$name])) {
            if (is_object($this->aliases[$name])) return $this->aliases[$name]::class;

            return $this->aliases[$name];
        }

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
        $resolvedAliases = array_map(fn ($i) => is_object($i) ? get_class($i) : $i, $this->aliases);

        if ($name = array_search($class, $resolvedAliases)) return $name;

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
        $namespace = str_replace(
            ['/', '\\'],
            '.',
            trim(trim(config('livewire.class_namespace')), '\\')
        );

        $class = str_replace(
            ['/', '\\'],
            '.',
            trim(trim($class, '/'), '\\')
        );

        $namespace = collect(explode('.', $namespace))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.');

        $fullName = str(collect(explode('.', $class))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.'));

        if ($fullName->startsWith('.')) {
            $fullName = $fullName->substr(1);
        }

        // If using an index component in a sub folder, remove the '.index' so the name is the subfolder name...
        if ($fullName->endsWith('.index')) {
            $fullName = $fullName->replaceLast('.index', '');
        }

        if ($fullName->startsWith($namespace)) {
            return (string) $fullName->substr(strlen($namespace) + 1);
        }

        return (string) $fullName;
    }
}
