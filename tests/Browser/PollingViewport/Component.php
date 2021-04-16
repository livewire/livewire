<?php

namespace Tests\Browser\PollingViewport;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $count = 0;

    public function render()
    {
        $this->count++;

        return View::file(__DIR__.'/view.blade.php');
    }
}
