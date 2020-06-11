<?php

namespace Livewire\Macros;

class ComponentRouteBinder
{
    protected $component;
    protected $router;

    public function __construct($component, $router)
    {
        if (gettype($component) === 'string') {
            $component = app('livewire')->getComponentClass($component);
        }

        if (! $component instanceof \ReflectionClass) {
            $component = new \ReflectionClass($component);
        }

        $this->component = $component;
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

        if ($this->component->hasMethod($method)) {
            // bind the method parameters to the request as if it's the route action
            $route->uses($this->component->getName().'@'.$method)->bind($request);

            // perform registered and implicit route bindings
            $this->router->substituteBindings($route);
            $this->router->substituteImplicitBindings($route);

            return $route->parameters();
        }

        return [];
    }
}
