<?php

namespace Livewire\Mechanisms;

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
