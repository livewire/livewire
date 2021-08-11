<?php

namespace Tests\Browser\Alpine;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component as BaseComponent;

class SimultaneousCallsComponent extends BaseComponent
{
    public function get()
    {
        return Str::random();
    }

    public function render()
    {
        return View::file(__DIR__.'/simultaneous-calls-component.blade.php');
    }
}
