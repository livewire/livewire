<?php

namespace Livewire\Macros;

use Illuminate\Support\Str;
use ReflectionClass;

class ComponentRouteBinder
{
    protected $componentReflector;
    protected $router;

    public function __construct($component, $router)
    {
        if (gettype($component) === 'string') {
            $component = app('livewire')->getComponentClass($component);
        }

        $this->componentReflector = $component instanceof ReflectionClass
            ? $component
            : new ReflectionClass($component);

        $this->router = $router;
    }

    public function retrieveCurrentMountBindings()
    {
        return $this->retrieveBindingsForMethod('mount');
    }

    public function retrieveBindingsForMethod($method, $route = null, $request = null)
    {
        // use a clone of the route to avoid side effects,
        $route = clone ($route ?: $this->router->getCurrentRoute());
        $request = $request ?: $this->router->getCurrentRequest();

        if ($this->componentReflector->hasMethod($method)) {
            // bind the method parameters to the request as if it's the route action
            $route->uses($this->componentReflector->getName().'@'.$method)->bind($request);

            $this->normalizeParameterNames($route, $method);

            // perform registered and implicit route bindings
            $this->router->substituteBindings($route);
            $this->router->substituteImplicitBindings($route);

            $this->substituteNormalizedParameterNames($route, $method);

            return $route->parameters();
        }

        return [];
    }

    protected function normalizeParameterNames($route, $method)
    {
        // Ensure router's substitution has snake case version of parameter to fall back on

        foreach ($this->componentReflector->getMethod($method)->getParameters() as $parameter) {
            if ($route->hasParameter($parameterName = $parameter->getName())) {
                continue;
            }

            if ($route->hasParameter($camelName = Str::camel($parameterName))) {
                $route->setParameter($parameterName, $route->parameter($camelName));
                $route->forgetParameter($camelName);
            }
        }
    }

    protected function substituteNormalizedParameterNames($route, $method)
    {
        // Handle cases where method parameter is not snake case

        foreach ($this->componentReflector->getMethod($method)->getParameters() as $parameter) {
            if ($route->hasParameter($parameterName = $parameter->getName())) {
                continue;
            }

            if ($route->hasParameter($snakeName = Str::snake($parameterName))) {
                $route->setParameter($parameterName, $route->parameter($snakeName));
                $route->forgetParameter($snakeName);
            }
        }
    }
}
