<?php

namespace Livewire;

use Illuminate\Container\BoundMethod;
use Illuminate\Contracts\Routing\UrlRoutable as ImplicitlyBindable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ReflectionClass;
use ReflectionNamedType;

class ImplicitlyBoundMethod extends BoundMethod
{
    protected static array $methodParamCountCache = [];

    public static function flushCache(): void
    {
        static::$methodParamCountCache = [];
    }

    public static function call($container, $callback, array $parameters = [], $defaultMethod = null)
    {
        if (is_array($callback) && is_object($callback[0]) && is_string($callback[1])) {
            $class = get_class($callback[0]);
            $method = $callback[1];
            $key = "{$class}@{$method}";

            if (! isset(static::$methodParamCountCache[$key])) {
                $reflector = new \ReflectionMethod($callback[0], $method);
                static::$methodParamCountCache[$key] = $reflector->getNumberOfParameters();
            }

            if (static::$methodParamCountCache[$key] === 0 && empty($parameters)) {
                if (! $container->hasMethodBinding($key)) {
                    return $callback[0]->{$method}();
                }
            }
        }

        return parent::call($container, $callback, $parameters, $defaultMethod);
    }

    protected static function getMethodDependencies($container, $callback, array $parameters = [])
    {
        return static::resolveMethodDependencies($container, $callback, $parameters)['positional'];
    }

    public static function resolveMethodDependencies($container, $callback, array $parameters = [])
    {
        $positional = [];
        $named = [];
        $paramIndex = 0;

        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            $parameterPosition = count($positional);

            static::substituteNameBindingForCallParameter($parameter, $parameters, $paramIndex);
            static::substituteImplicitBindingForCallParameter($container, $parameter, $parameters);
            static::addDependencyForCallParameter($container, $parameter, $parameters, $positional);

            $parameterDependencies = array_slice($positional, $parameterPosition);

            if ($parameterDependencies) {
                $named[$parameter->getName()] = $parameter->isVariadic()
                    ? $parameterDependencies
                    : $parameterDependencies[0];
            }
        }

        return [
            'positional' => array_values(array_merge($positional, $parameters)),
            'named' => $named,
        ];
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
        if (is_null($value)) {
            return null;
        }

        if (enum_exists($className)) {
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
        $type = $parameter->getType();
        if (! $type || ! method_exists($type, 'getName')) return false;

        return is_subclass_of($type->getName(), ImplicitlyBindable::class);
    }

    public static function isEnum($parameter)
    {
        $type = $parameter->getType();
        if (! $type || ! method_exists($type, 'getName')) return false;

        return enum_exists($type->getName());
    }
}
