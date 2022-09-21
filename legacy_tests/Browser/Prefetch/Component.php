<?php

namespace LegacyTests\Browser\Prefetch;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function render()
    {
        app('session')->put('count', app('session')->get('count') + 1);

        return View::file(__DIR__.'/view.blade.php');
    }
}
