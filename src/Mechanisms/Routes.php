<?php

namespace Livewire\Mechanisms;

use Illuminate\Support\Facades\Route;
use Livewire\Assets;

class Routes
{
    public function __invoke()
    {
        Route::get('/livewire/livewire.js', [Assets::class, 'source']);
    }
}
