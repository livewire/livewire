<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Drawer\ImplicitRouteBinding;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Mechanisms\ComponentDataStore;

class SupportPageComponents
{
    protected static $isPageComponentRequest = false;

    public function boot()
    {
        app('synthetic')->on('__invoke', function ($target) {
            return function () use ($target) {
                return static::renderPageComponent($target::class);
            };
        });

        app('synthetic')->on('render', function ($target, $view, $data) {
            if (! $view->livewireLayout) return;

            ComponentDataStore::set($target, 'layout', $view->livewireLayout['view']);
        });

        $this->registerViewMacros();
    }

    public static function renderPageComponent($component)
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

        /**
         * The whole "layout" system was hacked together for Laracon - super incomplete.
         */
        $layout = ComponentDataStore::get($instance, 'layout', 'components.layouts.app');

        $layout = str($layout)->after('components.');

        return \Illuminate\Support\Facades\Blade::render(<<<HTML
            <x-$layout>
                {!! \$content !!}
            </x-$layout>
        HTML, [
            'content' => $content
        ]);
    }

    public static function isRenderingPageComponent()
    {
        return static::$isPageComponentRequest;
    }

    public function registerViewMacros()
    {
        View::macro('layout', function ($view, $params = []) {
            $this->livewireLayout = [
                'type' => 'extends',
                'slotOrSection' => 'content',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        });
    }
}
