<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Mechanisms\ComponentDataStore;
use Livewire\Drawer\ImplicitRouteBinding;
use Illuminate\View\View;
use Illuminate\View\AnonymousComponent;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupportPageComponents
{
    protected static $isPageComponentRequest = false;

    public function boot()
    {
        app('synthetic')->on('__invoke', function ($target) {
            return function () use ($target) {
                return $this->renderPageComponent($target::class);
            };
        });

        app('synthetic')->on('render', function ($target, $view, $data) {
            if (! $view->livewireLayout) return;

            ComponentDataStore::set($target, 'layout', $view->livewireLayout);
        });

        $this->registerViewMacros();
    }

    function renderPageComponent($component)
    {
        static::$isPageComponentRequest = true;

        // We need to override it here. However, we can't remove the actual
        // param from the method signature as it would break inheritance.
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

        $instance = null;

        app('synthetic')->on('mount', $cb = function ($name, $params, $parent, $key, $slots, $hijack) use (&$instance) {
            return function ($target) use (&$instance) {
                return $instance = $target;
            };
        });

        $content = app('livewire')->mount($component, $params);

        app('synthetic')->off('mount', $cb);

        $layout = ComponentDataStore::get($instance, 'layout', []);

        $layout = [
            'view' => $layout['view'] ?? config('livewire.layout'),
            'type' => $layout['type'] ?? 'component',
            'params' => $layout['params'] ?? [],
            'slotOrSection' => $layout['slotOrSection'] ?? 'slot',
        ];

        if ($layout['type'] === 'component') {
            return \Illuminate\Support\Facades\Blade::render(<<<HTML
                @component(\$layout['view'], \$layout['params'])
                    @slot(\$layout['slotOrSection'])
                        {!! \$content !!}
                    @endslot
                @endcomponent
            HTML, [
                'content' => $content,
                'layout' => $layout,
            ]);
        } else {
            return \Illuminate\Support\Facades\Blade::render(<<<HTML
                @extends(\$layout['view'], \$layout['params'])

                @section(\$layout['slotOrSection'])
                    {!! \$content !!}
                @endsection
            HTML, [
                'content' => $content,
                'layout' => $layout,
            ]);
        }
    }

    public static function isRenderingPageComponent()
    {
        return static::$isPageComponentRequest;
    }

    public function registerViewMacros()
    {
        View::macro('layoutData', function ($data = []) {
            $this->livewireLayout['params'] = $data;

            return $this;
        });

        View::macro('section', function ($section) {
            $this->livewireLayout['slotOrSection'] = $section;

            return $this;
        });

        View::macro('slot', function ($slot) {
            $this->livewireLayout['slotOrSection'] = $slot;

            return $this;
        });

        View::macro('extends', function ($view, $params = []) {
            $this->livewireLayout = [
                'type' => 'extends',
                'slotOrSection' => 'content',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        });

        View::macro('layout', function ($view, $params = []) {
            $attributes = $params['attributes'] ?? [];
            unset($params['attributes']);

            if (is_subclass_of($view, \Illuminate\View\Component::class)) {
                $layout = app()->makeWith($view, $params);
                $view = $layout->resolveView()->name();
            } else {
                $layout = new AnonymousComponent($view, $params);
            }

            $layout->withAttributes($attributes);

            $this->livewireLayout = [
                'type' => 'component',
                'slotOrSection' => 'slot',
                'view' => $view,
                'params' => array_merge($params, $layout->data()),
            ];

            return $this;
        });
    }
}
