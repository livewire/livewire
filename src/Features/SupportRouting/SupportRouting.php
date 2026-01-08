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

            return Route::get($uri, function () use ($component) {
                return app()->call([
                    app('livewire')->new($component),
                    '__invoke',
                ]);
            });
        });
    }
}
