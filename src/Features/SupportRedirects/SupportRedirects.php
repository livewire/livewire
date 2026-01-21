<?php

namespace Livewire\Features\SupportRedirects;

use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Features\SupportRouting\LivewirePageController;
use Livewire\ComponentHook;
use Livewire\Component;
use function Livewire\on;

class SupportRedirects extends ComponentHook
{
    public static $redirectorCacheStack = [];
    public static $atLeastOneMountedComponentHasRedirected = false;

    public static function provide()
    {
        // Wait until all components have been processed...
        on('response', function ($response) {
            // If there was no redirect on a subsequent component update, clear flash session data.
            if (! static::$atLeastOneMountedComponentHasRedirected && app()->has('session.store')) {
                session()->forget(session()->get('_flash.new'));
            }
        });

        on('flush-state', function () {
            static::$atLeastOneMountedComponentHasRedirected = false;
        });
    }

    public function boot()
    {
        // Put Laravel's redirector aside and replace it with our own custom one.
        static::$redirectorCacheStack[] = app('redirect');

        app()->bind('redirect', function () {
            $redirector = app(Redirector::class)->component($this->component);

            if (app()->has('session.store')) {
                $redirector->setSession(app('session.store'));
            }

            return $redirector;
        });
    }

    public function dehydrate($context)
    {
        // Put the old redirector back into the container.
        app()->instance('redirect', array_pop(static::$redirectorCacheStack));

        $to = $this->storeGet('redirect');
        $usingNavigate = $this->storeGet('redirectUsingNavigate');

        if (is_subclass_of($to, Component::class)) {
            $to = static::resolveComponentUrl($to);
        }

        if ($to && ! app(HandleRequests::class)->isLivewireRequest()) {
            abort(redirect($to));
        }

        if (! $to) return;

        $context->addEffect('redirect', $to);
        $usingNavigate && $context->addEffect('redirectUsingNavigate', true);

        if (! $context->isMounting()) {
            static::$atLeastOneMountedComponentHasRedirected = true;
        }
    }

    public static function resolveComponentUrl(string $componentClass): string
    {
        // First try using Laravel's action URL resolver (works when component is registered directly on route)
        try {
            return url()->action($componentClass);
        } catch (\InvalidArgumentException $e) {
            // Component wasn't registered directly as a route action, continue to search routes
        }

        // Search through all routes to find one that uses this component via Route::livewire()
        foreach (Route::getRoutes() as $route) {
            $uses = $route->action['uses'] ?? null;

            if (! is_string($uses)) continue;

            // Check if this route uses LivewirePageController (indicates Route::livewire() was used)
            if (str_contains($uses, LivewirePageController::class)) {
                $routeComponent = $route->action['livewire_component'] ?? null;

                if (! $routeComponent) continue;

                // Resolve the component class name (handles both class strings and component names)
                $resolvedClass = is_string($routeComponent) && class_exists($routeComponent)
                    ? $routeComponent
                    : app('livewire.factory')->resolveComponentClass($routeComponent);

                if ($resolvedClass === $componentClass) {
                    return url($route->uri());
                }
            }
        }

        throw new \InvalidArgumentException("Unable to resolve URL for Livewire component [{$componentClass}]. Make sure the component is registered as a route.");
    }
}
