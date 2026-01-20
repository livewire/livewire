<?php

namespace Livewire\Features\SupportRouting;

use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;

class SupportRouting extends ComponentHook
{
    public static function provide()
    {
        Route::macro('livewire', function ($uri, $component) {
            if (is_object($component)) {
                app('livewire')->addComponent($component);
            }

            return tap(Route::get($uri, LivewirePageController::class), function ($route) use ($component) {
                $route->action['livewire_component'] = $component;
            });
        });
    }
}
