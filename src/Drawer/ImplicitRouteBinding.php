<?php

namespace Livewire\Drawer;

use ReflectionMethod;
use Livewire\Component;
use Illuminate\Support\Reflector;
use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Routing\UrlRoutable;
use Livewire\Drawer\Utils;

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
        $cache = $route->getAction('uses');

        // We'll set the route action to be the "mount" method from the chosen
        // Livewire component, to get the proper implicit bindings.
        $route->uses(get_class($component).'@mount');

        // This is normally handled in the "SubstituteBindings" middleware, but
        // because that middleware has already ran, we need to run them again.
        $this->container['router']->substituteImplicitBindings($route);

        $parameters = $route->resolveMethodDependencies($route->parameters(), new ReflectionMethod($component, 'mount'));

        // Restore the original route action.
        $route->uses($cache);

        return new Collection($parameters);
    }

    public function resolveComponentProps(Route $route, Component $component)
    {
        if (PHP_VERSION_ID < 70400) {
            return;
        }

        return $this->getPublicPropertyTypes($component)
            ->intersectByKeys($route->parametersWithoutNulls())
            ->map(function ($className, $propName) use ($route) {
                $resolved = $this->resolveParameter($route, $propName, $className);

                // We'll also pass the resolved model back to the route
                // so that it can be used for any depending bindings
                $route->setParameter($propName, $resolved);

                return $resolved;
            });
    }

    public function getPublicPropertyTypes($component)
    {
        if (PHP_VERSION_ID < 70400) {
            return new Collection();
        }

        return collect(Utils::getPublicPropertiesDefinedOnSubclass($component))
            ->map(function ($value, $name) use ($component) {
                return Reflector::getParameterClassName(new \ReflectionProperty($component, $name));
            })
            ->filter();
    }

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
