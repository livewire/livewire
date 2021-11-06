<?php

namespace Livewire;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException;

class LivewirePropertyManager
{
    /**
     * Contains all registered properties.
     */
    protected $properties = [];

    /**
     * Register custom properties and providing a class on how to resolve this property.
     * There are two ways to register a property:
     *
     * LivewireProperty::register(CustomPublicClass::class, CustomResolverClass::class);
     *
     * // OR
     *
     * LivewireProperty::register([
     *     CustomPublicClass::class => CustomResolverClass::class,
     *     CustomPublicClass2::class => CustomResolverClass::class,
     * ]);
     */
    public function register(...$args)
    {
        if ($this->containsMultipleInstances($args)) {
            $this->registerMultipleProperties($args[0]);
        } else {
            [$class, $resolver] = $args;
            $this->registerSingleProperty($class, $resolver);
        }

        return $this;
    }

    /**
     * Return all registered properties available.
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Return all registered properties available.
     */
    public function all()
    {
        return $this->properties();
    }

    /**
     * Check if the property manager has a resolver for a specific class.
     */
    public function has($classToCheck)
    {
        if (empty($classToCheck) || is_array($classToCheck)) {
            return false;
        }

        try { // In case an error will be thrown, we know that we can return false.
            $className = (new \ReflectionClass($classToCheck))->getName();
        } catch (\Exception $e) {
            return false;
        }

        return collect($this->properties())->contains(function($value, $key) use ($className) {
            return $className === $key;
        });
    }

    /**
     * Some syntactic sugar to check if a resolver has not been registered.
     */
    public function hasNot($classToCheck)
    {
        return ! $this->has($classToCheck);
    }

    /**
     * Dehydrate a given value by finding the belonging resolver.
     *
     * A value which needs to be dehydrated does contain the
     * resolver class, which is why it's simply enough
     * to use the `get_class` function to fetch it.
     */
    public function dehydrate($value)
    {
        if ($this->hasNot($value)) {
            return null;
        }

        $resolver = $this->properties[get_class($value)];

        return (new $resolver($value))->dehydrate($value);
    }

    /**
     * Hydrate a value from a belonging instance and property.
     *
     * If hydrating a value, we need to use reflection to
     * determine the resolver class first. After
     * using reflection, we can fetch it
     * and hydrate the value directly.
     */
    public function hydrate($instance, $property, $value)
    {
        $class = (new \ReflectionClass($instance))
            ->getProperty($property)
            ->getType()
            ->getName();

        if ($this->hasNot($class)) {
            return null;
        }

        $resolver = $this->properties[$class];

        return $resolver::hydrate($value);
    }

    private function containsMultipleInstances($args)
    {
        return is_array($args[0]);
    }

    private function registerSingleProperty($class, $resolver)
    {
        throw_unless(is_subclass_of($resolver, PropertyHandler::class), new CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException());

        $this->properties[$class] = $resolver;
    }

    private function registerMultipleProperties($properties)
    {
        foreach ($properties as $class => $resolver) {
            $this->register($class, $resolver);
        }
    }
}
