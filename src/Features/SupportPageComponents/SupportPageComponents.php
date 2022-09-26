<?php

namespace Livewire\Features\SupportPageComponents;

use Illuminate\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\AnonymousComponent;
use Livewire\Drawer\ImplicitRouteBinding;
use Livewire\Mechanisms\ComponentDataStore;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupportPageComponents
{
    protected static $isPageComponentRequest = false;

    public function boot()
    {
        $this->registerLayoutViewMacros();

        $this->whenALivewireComponentIsUsedAsARoute(function ($component) {
            $content = $instance = null;

            $layoutConfig = $this->interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration(function () use (&$content, &$instance, $component) {
                $params = $this->gatherMountMethodParamsFromRouteParameters($component);

                [$content, $instance] = $this->mountAndRenderTheComponent($component, $params);
            });

            $layoutConfig = $this->mergeLayoutDefaults($layoutConfig);

            return $this->renderContentsIntoLayout($content, $layoutConfig);
        });
    }

    public static function isRenderingPageComponent()
    {
        return static::$isPageComponentRequest;
    }

    public function registerLayoutViewMacros()
    {
        View::macro('layoutData', function ($data = []) {
            $this->layoutConfig['params'] = $data;

            return $this;
        });

        View::macro('section', function ($section) {
            $this->layoutConfig['slotOrSection'] = $section;

            return $this;
        });

        View::macro('slot', function ($slot) {
            $this->layoutConfig['slotOrSection'] = $slot;

            return $this;
        });

        View::macro('extends', function ($view, $params = []) {
            $this->layoutConfig = [
                'type' => 'extends',
                'slotOrSection' => 'content',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        });

        View::macro('layout', function ($view, $params = []) {
            $this->layoutConfig = [
                'type' => 'component',
                'slotOrSection' => 'slot',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        });
    }

    function whenALivewireComponentIsUsedAsARoute($callback)
    {
        // Here's we're hooking into the "__invoke" method being called on a component.
        // This way, users can pass Livewire components into Routes as if they were
        // simple invokable controllers. Ex: Route::get('...', SomeLivewireComponent::class);
        app('synthetic')->on('__invoke', function ($target) use ($callback) {
            return function () use ($target, $callback) {
                static::$isPageComponentRequest = true;

                return $callback($target);
            };
        });
    }

    function interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration($callback)
    {
        $layoutConfig = null;

        $handler = function ($target, $view, $data) use (&$layoutConfig) {
            // Here, ->layoutConfig is set from the layout view macros...
            if (! $view->layoutConfig) return;

             $layoutConfig = $view->layoutConfig;
        };

        app('synthetic')->on('render', $handler);

        $callback();

        app('synthetic')->off('render', $handler);

        return $layoutConfig;
    }

    function mountAndRenderTheComponent($component, $params)
    {
        return app('livewire')->mount($component::class, $params);
    }

    function gatherMountMethodParamsFromRouteParameters($component)
    {
        // This allows for route parameters like "slug" in /post/{slug},
        // to be passed into a Livewire component's mount method...
        $route = request()->route();

        try {
            $params = (new ImplicitRouteBinding(app()))
                ->resolveAllParameters($route, new $component);
        } catch (ModelNotFoundException $exception) {
            if (method_exists($route,'getMissing') && $route->getMissing()) {
                return $route->getMissing()(request());
            }

            throw $exception;
        }

        return $params;
    }

    function mergeLayoutDefaults($layoutConfig)
    {
        $defaultLayoutConfig = [
            'view' => config('livewire.layout'),
            'type' => 'component',
            'params' => [],
            'slotOrSection' => 'slot',
        ];

        $layoutConfig = array_merge($defaultLayoutConfig, $layoutConfig ?: []);

        return $this->normalizeViewNameAndParamsForBladeComponents($layoutConfig);
    }

    function normalizeViewNameAndParamsForBladeComponents($layoutConfig)
    {
        // If a user passes the class name of a Blade component to the
        // layout macro (or uses inside their config), we need to
        // convert it to it's "view" name so Blade doesn't break.
        $view = $layoutConfig['view'];
        $params = $layoutConfig['params'];

        $attributes = $params['attributes'] ?? [];
        unset($params['attributes']);

        if (is_subclass_of($view, \Illuminate\View\Component::class)) {
            $layout = app()->makeWith($view, $params);
            $view = $layout->resolveView()->name();
        } else {
            $layout = new AnonymousComponent($view, $params);
        }

        $layout->withAttributes($attributes);

        $params = array_merge($params, $layout->data());

        $layoutConfig['view'] = $view;
        $layoutConfig['params'] = $params;

        return $layoutConfig;
    }

    function renderContentsIntoLayout($content, $layoutConfig)
    {
        if ($layoutConfig['type'] === 'component') {
            return Blade::render(<<<'HTML'
                @component($layout['view'], $layout['params'])
                    @slot($layout['slotOrSection'])
                        {!! $content !!}
                    @endslot
                @endcomponent
            HTML, [
                'content' => $content,
                'layout' => $layoutConfig,
            ]);
        } else {
            return Blade::render(<<<'HTML'
                @extends($layout['view'], $layout['params'])

                @section($layout['slotOrSection'])
                    {!! $content !!}
                @endsection
            HTML, [
                'content' => $content,
                'layout' => $layoutConfig,
            ]);
        }
    }
}
