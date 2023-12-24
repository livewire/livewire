<?php

namespace Livewire\Mechanisms;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\LivewireServiceProvider;

abstract class Mechanism
{
    function register()
    {
        app()->instance(static::class, $this);
    }

    function boot()
    {
        //
    }
}
