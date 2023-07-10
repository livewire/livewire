<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Drawer\Utils;

use function Livewire\on;

class TestingInitialRender
{
    function __construct(
        protected $requester,
    ) {}

    static function make($requester, $name, $params = [], $fromQueryString = [])
    {
        $instance = new static($requester);

        return $instance->renameme($name, $params, $fromQueryString);
    }

    function renameme($name, $params, $fromQueryString = []) {
        $uri = '/livewire-unit-test-endpoint/'.str()->random(20);

        $this->registerRouteBeforeExistingRoutes($uri, function () use ($name, $params) {
            return \Illuminate\Support\Facades\Blade::render('@livewire($name, $params)', [
                'name' => $name,
                'params' => $params,
            ]);
        });

        $componentInstance = null;
        $componentView = null;

        $offA = on('dehydrate', function ($component) use (&$componentInstance) {
            $componentInstance = $component;
        });

        $offB = on('render', function ($component, $view) use (&$componentView) {
            return function () use ($view, &$componentView) {
                $componentView = $view;
            };
        });

        $response = $this->requester->temporarilyDisableExceptionHandlingAndMiddleware(function ($requester) use ($uri, $fromQueryString) {
            return $requester->call('GET', $uri, $fromQueryString);
        });

        app('livewire')->flushState();

        $offA(); $offB();

        $html = $response->getContent();

        $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');
        $effects = Utils::extractAttributeDataFromHtml($html, 'wire:effects');

        return new TestingState($componentInstance, $response, $componentView, $html, $snapshot, $effects);
    }

    private function registerRouteBeforeExistingRoutes($path, $closure)
    {
        // To prevent this route from overriding wildcard routes registered within the application,
        // We have to make sure that this route is registered before other existing routes.
        $livewireTestingRoute = new \Illuminate\Routing\Route(['GET', 'HEAD'], $path, $closure);

        $existingRoutes = app('router')->getRoutes();

        // Make an empty collection.
        $runningCollection = new \Illuminate\Routing\RouteCollection;

        // Add this testing route as the first one.
        $runningCollection->add($livewireTestingRoute);

        // Now add the existing routes after it.
        foreach ($existingRoutes as $route) {
            $runningCollection->add($route);
        }

        // Now set this route collection as THE route collection for the app.
        app('router')->setRoutes($runningCollection);
    }
}
