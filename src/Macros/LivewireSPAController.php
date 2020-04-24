<?php

namespace Livewire\Macros;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use ReflectionClass;

class LivewireSPAController
{
    /**
     * @param Route $route
     * @param Router $router
     * @param string $method
     * @param array $parameters
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     * @throws \ReflectionException
     */
    public function __call(Route $route, Router $router, $method, $parameters)
    {
        $componentName = $method;

        $componentClass = app('livewire')->getComponentClass($componentName);
        $reflected = new ReflectionClass($componentClass);

        $componentParameters = $reflected->hasMethod('mount')
            ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $router))->retrieveBindings()
            : [];

        return view(
            __DIR__ . '/livewire-view.blade.php',
            [
                'layout' => $route->getAction('layout') ?? 'layouts.app',
                'section' => $route->getAction('section') ?? 'content',
                'component' => $componentName,
                'componentParameters' => $componentParameters,
            ]
        )->with($route->layoutParamsFromLivewire ?? []);
    }
}
