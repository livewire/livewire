<?php

namespace Livewire\Macros;

class PretendClassMethodIsControllerMethod
{
    protected $method;
    protected $router;

    public function __construct($method, $router)
    {
        $this->method = $method;
        $this->router = $router;
    }

    public function retrieveBindings()
    {
        $route = $this->router->current();

        // Cache the current route action (this callback actually), just to be safe.
        $cache = $route->getAction('uses');

        // We'll set the route action to be the "mount" method from the chosen
        // Livewire component, to get the proper implicit bindings.
        $route->uses($this->method->class.'@'.$this->method->name);

        // This is normally handled in the "SubstituteBindings" middleware, but
        // because that middleware has already ran, we need to run them again.
        $this->router->substituteImplicitBindings($route);

        $options = $route->resolveMethodDependencies($route->parameters(), $this->method);

        // Restore the original route action.
        $route->uses($cache);

        return $options;
    }
}
