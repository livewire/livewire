<?php

namespace Livewire\Mechanisms;

use Illuminate\Routing\Route;

class Routes
{
    public function __invoke()
    {
        Route::get('/livewire/livewire.js.map', [LivewireJavaScriptAssets::class, 'maps']);
        Route::get('/livewire/livewire.js', [LivewireJavaScriptAssets::class, 'source']);
    }
}
