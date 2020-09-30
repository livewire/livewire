<?php

namespace Tests\Browser\Alpine\Entangle;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $items = ['foo', 'bar'];

    public $showBob = false;
    public $bob = 'before';

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
