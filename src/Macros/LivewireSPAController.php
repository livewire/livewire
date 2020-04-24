<?php

namespace Livewire\Macros;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use ReflectionClass;

class LivewireSPAController
{
    /**
     * @var Route
     */
    private $route;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Route $route, Router $router)
    {
        $this->route = $route;
        $this->router = $router;
    }

    /**
     * @param string $method
     * @param array $parameters
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function __call($method, $parameters)
    {
        $componentName = $method;

        $componentClass = app('livewire')->getComponentClass($componentName);
        $reflected = new ReflectionClass($componentClass);

        $componentParameters = $reflected->hasMethod('mount')
            ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $this->router))->retrieveBindings()
            : [];

        return view()->file(__DIR__ . '/livewire-view.blade.php', [
            'layout' => $this->route->getAction('layout') ?? 'layouts.app',
            'section' => $this->route->getAction('section') ?? 'content',
            'component' => $componentName,
            'componentParameters' => $componentParameters,
        ])->with($this->route->layoutParamsFromLivewire ?? []);
    }
}
