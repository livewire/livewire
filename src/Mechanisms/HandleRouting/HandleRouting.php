<?php

namespace Livewire\Mechanisms\HandleRouting;

use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\Mechanism;

class HandleRouting extends Mechanism
{
    public function register()
    {
        parent::register();

        Route::macro('livewire', function ($uri, $component): IlluminateRoute {
            if (is_object($component)) {
                app('livewire')->addComponent($component);
            }

            return tap(Route::get($uri, LivewirePageController::class), function ($route) use ($component) {
                $route->action['livewire_component'] = $component;
            });
        });
    }
}
