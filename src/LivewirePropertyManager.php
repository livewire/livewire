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
     * Register custom properties and provide a belonging resolver to resolve a property type.
     * There are two ways to register properties:
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
     * Return all available properties and their resolver classes.
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Simply an alternative method name to return all properties.
     */
    public function all()
    {
        return $this->properties();
    }

    /**
     * Get a belonging resovler from the given class.
     */
    public function get(?string $class)
    {
        if (is_null($class)) {
            return null;
        }

        if (! array_key_exists($class, $this->properties)) {
            return null;
        }

        return $this->properties[$class];
    }

    /**
     * Check if the property class has as a property.
     */
    public function has($class)
    {
        if (empty($class) || is_array($class)) {
            return false;
        }

        if (is_object($class)) {
            $class = (new \ReflectionClass($class))->getName();
        }

        return (bool) $this->get($class);
    }

    /**
     * Some syntactic sugar to check if a class does not exist.
     */
    public function hasNot($class)
    {
        return ! $this->has($class);
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
