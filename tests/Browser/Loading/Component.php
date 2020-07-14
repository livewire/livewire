<?php

namespace Tests\Browser\Loading;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function sleep($milliseconds)
    {
        usleep($milliseconds * 1000);
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
