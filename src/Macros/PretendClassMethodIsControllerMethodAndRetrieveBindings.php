<?php

namespace Livewire\Macros;

class PretendClassMethodIsControllerMethodAndRetrieveBindings
{
    public function __invoke($method, $router)
    {
        $route = $router->current();

        // Cache the current route action (this callback actually), just to be safe.
        $cache = $route->getAction('uses');

        // We'll set the route action to be the "created" method from the chosen
        // Livewire component, to get the proper implicit bindings.
        $route->uses($method->class . '@' . $method->name);

        // This is normally handled in the "SubstituteBindings" middleware, but
        // because that middleware has already ran, we need to run them again.
        $router->substituteBindings($route);
        $router->substituteImplicitBindings($route);

        $options = $route->resolveMethodDependencies($route->parameters(), $method);

        // Restore the original route action.
        $route->uses($cache);

        return array_values($options);
    }
}
