<?php

namespace Livewire\Mechanisms;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\LivewireServiceProvider;

abstract class Mechanism
{
    protected $singleton = true;

    function register($provider)
    {
        if ($this->singleton) app()->instance(static::class, $this);
    }

    function boot()
    {
        //
    }
}
