<?php

namespace Livewire;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ImplicitRouteBinding
{
    // Can be removed if Laravel #34064 is merged
    protected $container;

    // Can be removed if Laravel #34064 is merged
    protected static function getParameterName($name, $parameters)
    {
        if (array_key_exists($name, $parameters)) {
            return $name;
        }

        if (array_key_exists($snakedName = Str::snake($name), $parameters)) {
            return $snakedName;
        }
    }

    // Can be removed if Laravel #34064 is merged
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function resolveComponentProps(Route $route, Component $component)
    {
        if (PHP_VERSION_ID < 70400) {
            return;
        }

        $routeProps = $component->getPublicPropertyTypes()->intersectByKeys($route->parametersWithoutNulls());
        foreach ($routeProps as $propName => $className) {
            $component->{$propName} = $this->resolveParameter($route, $propName, $className);
        }
    }

    // Can be removed if Laravel #34064 is merged
    protected function resolveParameter($route, $parameterName, $parameterClassName)
    {
        $parameterValue = $route->parameter($parameterName);

        if ($parameterValue instanceof UrlRoutable) {
            return $parameterValue;
        }

        $instance = $this->container->make($parameterClassName);

        $parent = $route->parentOfParameter($parameterName);

        if ($parent instanceof UrlRoutable && array_key_exists($parameterName, $route->bindingFields())) {
            if (! $model = $parent->resolveChildRouteBinding(
                $parameterName, $parameterValue, $route->bindingFieldFor($parameterName)
            )) {
                throw (new ModelNotFoundException())->setModel(get_class($instance), [$parameterValue]);
            }
        } elseif (! $model = $instance->resolveRouteBinding($parameterValue, $route->bindingFieldFor($parameterName))) {
            throw (new ModelNotFoundException())->setModel(get_class($instance), [$parameterValue]);
        }

        return $model;
    }
}
