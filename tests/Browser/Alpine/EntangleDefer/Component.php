<?php

namespace Tests\Browser\Alpine\EntangleDefer;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $livewireShow = false;

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
