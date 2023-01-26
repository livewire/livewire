<?php

namespace Livewire\Features\SupportPagination;

use function Livewire\invade;
use function Livewire\on;

class SupportPagination
{
    function boot($serviceProvider)
    {
        $provider = invade($serviceProvider);

        $provider->loadViewsFrom(__DIR__.'/views', 'livewire');

        $paths = [__DIR__.'/views' => resource_path('views/vendor/livewire')];

        $provider->publishes($paths, 'livewire');
        $provider->publishes($paths, 'livewire:pagination');
    }
}
