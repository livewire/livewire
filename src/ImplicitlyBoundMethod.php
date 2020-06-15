<?php

namespace Livewire;

use Closure;
use Illuminate\Container\BoundMethod;
use Illuminate\Container\Util;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Routing\UrlRoutable as ImplicitlyBindable;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

class ImplicitlyBoundMethod extends BoundMethod
{
    /**
     * Get all dependencies for a given method. Override the parent method
     * to substitute implicit and name/value bindings for each expected
     * parameter.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  callable|string  $callback
     * @param  array  $parameters
     * @return array
     *
     * @throws \ReflectionException
     */
    protected static function getMethodDependencies($container, $callback, array $parameters = [])
    {
        $dependencies = [];
        $paramIndex = 0;

        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            static::substituteNameBindingForCallParameter($parameter, $parameters, $paramIndex);
            static::substituteImplicitBindingForCallParameter($container, $parameter, $parameters, $dependencies);
            static::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies);
        }

        return array_merge($dependencies, $parameters);
    }

    /**
     * Ensure the given call parameter value is bound to the parameter by name.
     * Handles cases where a simple list of parameters (having numeric keys)
     * is provided and the sequence implies the binding.
     *
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * @param  int    $paramIndex
     * @return void
     */
    protected static function substituteNameBindingForCallParameter(
                                $parameter, array &$parameters, int &$paramIndex)
    {
        // check if we have a candidate for name/value binding
        if (! array_key_exists($paramIndex, $parameters)) {
            return;
        }

        if ($parameter->isVariadic()) {
            // this last param will pick up the rest - reindex any remaining parameters
            $parameters = array_merge(
                array_filter($parameters, function($key) { return ! is_int($key); }, ARRAY_FILTER_USE_KEY),
                array_values(array_filter($parameters, function($key) { return is_int($key); }, ARRAY_FILTER_USE_KEY))
            );

            return;
        }

        // stop if this one is due for dependency injection
        if (! is_null($className = static::getClassForDependencyInjection($parameter))
            && ! $parameters[$paramIndex] instanceof $className) {
            return;
        }

        if (! array_key_exists($paramName = $parameter->getName(), $parameters)) {
            // have a parameter value that is bound by sequential order
            // and not yet bound by name, so bind it to parameter name

            $parameters[$paramName] = $parameters[$paramIndex];
            unset($parameters[$paramIndex]);
            $paramIndex++;
        }

    }

    /**
     * Perform implicit binding for the given call parameter.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * @return void
     */
    protected static function substituteImplicitBindingForCallParameter(
                                    $container, $parameter, array &$parameters)
    {
        $paramName = $parameter->getName();

        // check if we have a candidate for implicit binding
        if (is_null($className = static::getClassForImplicitBinding($parameter))) {
            return;
        }

        // Check if the value we have for this param is an instance
        // of the desired class, attempt implicit binding if not
        if (array_key_exists($paramName, $parameters)
            && ! $parameters[$paramName] instanceof $className) {

            $parameters[$paramName] = static::getImplicitBinding($container, $className, $parameters[$paramName]);

        } elseif (array_key_exists($className, $parameters)
            && ! $parameters[$className] instanceof $className) {

            $parameters[$className] = static::getImplicitBinding($container, $className, $parameters[$className]);
        }
    }

    /**
     * Get the dependency injectable class name for the given call parameter.
     *
     * @param  \ReflectionParameter  $parameter
     * @return string|null
     */
    protected static function getClassForDependencyInjection($parameter)
    {
        if (! is_null($className = Util::getParameterClassName($parameter))
            && ! $parameter->getClass()->implementsInterface(ImplicitlyBindable::class)) {
            return $className;
        }

        return null;
    }

    /**
     * Get the implicitly bindable class name for the given call parameter.
     *
     * @param  \ReflectionParameter  $parameter
     * @return string|null
     */
    protected static function getClassForImplicitBinding($parameter)
    {
        if (! is_null($className = Util::getParameterClassName($parameter))
            && $parameter->getClass()->implementsInterface(ImplicitlyBindable::class)) {
            return $className;
        }

        return null;
    }

    /**
     * Get the instance of the given class implied by the given value.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  string  $className
     * @param  mixed  $value
     * @return mixed
     */
    protected static function getImplicitBinding($container, $className, $value)
    {
        return $container->make($className)->resolveRouteBinding($value);
    }
}
