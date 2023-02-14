<?php

namespace Livewire\Features\SupportPagination;

use function Livewire\invade;
use Livewire\ComponentHook;

class SupportPagination extends ComponentHook
{
    static function provide()
    {
        $provider = invade(app('livewire.provider'));

        $provider->loadViewsFrom(__DIR__.'/views', 'livewire');

        $paths = [__DIR__.'/views' => resource_path('views/vendor/livewire')];

        $provider->publishes($paths, 'livewire');
        $provider->publishes($paths, 'livewire:pagination');
    }
}
