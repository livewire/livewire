<?php

namespace Livewire\Drawer;

use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use BackedEnum;
use ReflectionClass;
use ReflectionMethod;
use Livewire\Component;
use Illuminate\Support\Reflector;
use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Routing\UrlRoutable;

/**
 * This class mirrors the functionality of Laravel's Illuminate\Routing\ImplicitRouteBinding class.
 */
class ImplicitRouteBinding
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function resolveAllParameters(Route $route, Component $component)
    {
        $params = $this->resolveMountParameters($route, $component);
        $props = $this->resolveComponentProps($route, $component);

        return $params->merge($props)->all();
    }

    public function resolveMountParameters(Route $route, Component $component)
    {
        if (! method_exists($component, 'mount')) {
            return new Collection();
        }

        // Cache the current route action (this callback actually), just to be safe.
        $cache = $route->getAction();

        // We'll set the route action to be the "mount" method from the chosen
        // Livewire component, to get the proper implicit bindings.
        $route->uses(get_class($component).'@mount');

        try {
            // This is normally handled in the "SubstituteBindings" middleware, but
            // because that middleware has already ran, we need to run them again.
            $this->container['router']->substituteImplicitBindings($route);

            $parameters = $route->resolveMethodDependencies($route->parameters(), new ReflectionMethod($component, 'mount'));

            // Restore the original route action...
            $route->setAction($cache);
        } catch(\Exception $e) {
            // Restore the original route action before an exception is thrown...
            $route->setAction($cache);

            throw $e;
        }

        return new Collection($parameters);
    }

    public function resolveComponentProps(Route $route, Component $component)
    {
        return $this->getPublicPropertyTypes($component)
            ->intersectByKeys($route->parametersWithoutNulls())
            ->map(function ($className, $propName) use ($route) {
                // If typed public property, resolve the class
                if ($className) {
                    $resolved = $this->resolveParameter($route, $propName, $className);

                    // We'll also pass the resolved model back to the route
                    // so that it can be used for any depending on bindings
                    $route->setParameter($propName, $resolved);

                    return $resolved;
                }

                // Otherwise, just return the route parameter
                return $route->parameter($propName);
            });
    }

    public function getPublicPropertyTypes($component)
    {
        return collect(Utils::getPublicPropertiesDefinedOnSubclass($component))
            ->map(function ($value, $name) use ($component) {
                return Reflector::getParameterClassName(new \ReflectionProperty($component, $name));
            });
    }

    protected function resolveParameter($route, $parameterName, $parameterClassName)
    {
        $parameterValue = $route->parameter($parameterName);

        if ($parameterValue instanceof UrlRoutable) {
            return $parameterValue;
        }

        if($enumValue = $this->resolveEnumParameter($parameterValue, $parameterClassName)) {
            return $enumValue;
        }

        $instance = $this->container->make($parameterClassName);

        $parent = $route->parentOfParameter($parameterName);

        if ($parent instanceof UrlRoutable && ($route->enforcesScopedBindings() || array_key_exists($parameterName, $route->bindingFields()))) {
            $model = $parent->resolveChildRouteBinding($parameterName, $parameterValue, $route->bindingFieldFor($parameterName));
        } else {
            if ($route->allowsTrashedBindings()) {
                $model = $instance->resolveSoftDeletableRouteBinding($parameterValue, $route->bindingFieldFor($parameterName));
            } else {
                $model = $instance->resolveRouteBinding($parameterValue, $route->bindingFieldFor($parameterName));
            }
        }

        if (! $model) {
            throw (new ModelNotFoundException())->setModel(get_class($instance), [$parameterValue]);
        }

        return $model;
    }

    protected function resolveEnumParameter($parameterValue, $parameterClassName)
    {
        if ($parameterValue instanceof BackedEnum) {
            return $parameterValue;
        }

        if ((new ReflectionClass($parameterClassName))->isEnum()) {
            $enumValue = $parameterClassName::tryFrom($parameterValue);

            if (is_null($enumValue)) {
                throw new BackedEnumCaseNotFoundException($parameterClassName, $parameterValue);
            }

            return $enumValue;
        }

        return null;
    }
}
