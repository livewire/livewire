<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Drawer\ImplicitRouteBinding;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

        return \Illuminate\Support\Facades\Blade::render(<<<HTML
            <x-layouts.app>
                {!! \$contents !!}
            </x-layouts.app>
        HTML, [
            'contents' => app('livewire')->mount($component, $params),
        ]);
    }

    public static function isRenderingPageComponent()
    {
        return static::$isPageComponentRequest;
    }
}
