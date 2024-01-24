<?php

namespace Livewire;

use Illuminate\Container\BoundMethod;
use Illuminate\Contracts\Routing\UrlRoutable as ImplicitlyBindable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ReflectionClass;
use ReflectionNamedType;
use UnitEnum;

class ImplicitlyBoundMethod extends BoundMethod
{
    protected static function getMethodDependencies($container, $callback, array $parameters = [])
    {
        $dependencies = [];
        $paramIndex = 0;

        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            static::substituteNameBindingForCallParameter($parameter, $parameters, $paramIndex);
            static::substituteImplicitBindingForCallParameter($container, $parameter, $parameters);
            static::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies);
        }

        return array_values(array_merge($dependencies, $parameters));
    }

    protected static function substituteNameBindingForCallParameter($parameter, array &$parameters, int &$paramIndex)
    {
        // check if we have a candidate for name/value binding
        if (! array_key_exists($paramIndex, $parameters)) {
            return;
        }

        if ($parameter->isVariadic()) {
            // this last param will pick up the rest - reindex any remaining parameters
            $parameters = array_merge(
                array_filter($parameters, function ($key) { return ! is_int($key); }, ARRAY_FILTER_USE_KEY),
                array_values(array_filter($parameters, function ($key) { return is_int($key); }, ARRAY_FILTER_USE_KEY))
            );

            return;
        }

        // stop if this one is due for dependency injection
        if (! is_null($className = static::getClassForDependencyInjection($parameter)) && ! $parameters[$paramIndex] instanceof $className) {
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

    protected static function substituteImplicitBindingForCallParameter($container, $parameter, array &$parameters)
    {
        $paramName = $parameter->getName();

        // check if we have a candidate for implicit binding
        if (is_null($className = static::getClassForImplicitBinding($parameter))) {
            return;
        }

        // Check if the value we have for this param is an instance
        // of the desired class, attempt implicit binding if not
        if (array_key_exists($paramName, $parameters) && ! $parameters[$paramName] instanceof $className) {
            $parameters[$paramName] = static::getImplicitBinding($container, $className, $parameters[$paramName]);
        } elseif (array_key_exists($className, $parameters) && ! $parameters[$className] instanceof $className) {
            $parameters[$className] = static::getImplicitBinding($container, $className, $parameters[$className]);
        }
    }

    protected static function getClassForDependencyInjection($parameter)
    {
        $className = static::getParameterClassName($parameter);

        if (is_null($className)) return null;

        if (static::isEnum($parameter)) return null;

        if (! static::implementsInterface($parameter)) return $className;

        return null;
    }

    protected static function getClassForImplicitBinding($parameter)
    {
        $className = static::getParameterClassName($parameter);

        if (is_null($className)) return null;

        if (static::isEnum($parameter)) return $className;

        if (static::implementsInterface($parameter)) return $className;

        return null;
    }

    protected static function getImplicitBinding($container, $className, $value)
    {
        if ((new ReflectionClass($className))->isEnum()) {
            return $className::tryFrom($value);
        }

        $model = $container->make($className)->resolveRouteBinding($value);

        if (! $model) {
            throw (new ModelNotFoundException)->setModel($className, [$value]);
        }

        return $model;
    }

    public static function getParameterClassName($parameter)
    {
        $type = $parameter->getType();

        if (! $type) return null;

        if (! $type instanceof ReflectionNamedType) return null;

        return (! $type->isBuiltin()) ? $type->getName() : null;
    }

    public static function implementsInterface($parameter)
    {
        return (new ReflectionClass($parameter->getType()->getName()))->implementsInterface(ImplicitlyBindable::class);
    }

    public static function isEnum($parameter)
    {
        return (new ReflectionClass($parameter->getType()->getName()))->isEnum();
    }
}
