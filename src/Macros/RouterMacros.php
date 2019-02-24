<?php

namespace Livewire\Macros;

use Illuminate\Routing\RouteRegistrar;

class RouterMacros
{
    public function layout()
    {
        return function ($layout) {
            return (new class($this) extends RouteRegistrar {
                public function __construct(\Illuminate\Routing\Router $router)
                {
                    array_push($this->allowedAttributes, 'layout', 'section');

                    parent::__construct($router);

                    return $this;
                }
            })->layout($layout);
        };
    }

    public function section()
    {
        return function ($section) {
            return (new class($this) extends RouteRegistrar {
                public function __construct(\Illuminate\Routing\Router $router)
                {
                    array_push($this->allowedAttributes, 'layout', 'section');

                    parent::__construct($router);

                    return $this;
                }
            })->section($section);
        };
    }

    public function livewire()
    {
        return function ($uri, $component) {
            return $this->get($uri, function (...$params) use ($component) {
                $componentClass = app('livewire')->getComponentClass($component);

                $route = $this->current();
                // Cache the current route action (this callback actually), just to be safe.
                $cache = $route->getAction('uses');

                // We'll set the route action to be the "created" method from the chosen
                // Livewire component, to get the proper implicit bindings.
                $route->uses($componentClass . '@created');
                // This is normally handled in the "SubstituteBindings" middleware, but
                // because that middleware has already ran, we need to run them again.
                $this->substituteBindings($route);
                $this->substituteImplicitBindings($route);

                // Now we take all that we have gathered and convert it into a nice
                // array of parameters to pass into the "created" method.
                if ((new \ReflectionClass($componentClass))->hasMethod('created')) {
                    $method = (new \ReflectionClass($componentClass))->getMethod('created');
                    $options = $route->resolveMethodDependencies($route->parameters(), $method);
                } else {
                    $options = [];
                }

                // Restore the original route action.
                $route->uses($cache);

                return app('view')->file(__DIR__ . '/livewire-view.blade.php', [
                    'layout' => $route->getAction('layout') ?? 'layouts.app',
                    'section' => $route->getAction('section') ?? 'content',
                    'component' => $componentClass,
                    'componentOptions' => array_values($options),
                ]);
            });
        };
    }
}
