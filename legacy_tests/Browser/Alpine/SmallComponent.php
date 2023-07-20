<?php

namespace LegacyTests\Browser\Alpine;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class SmallComponent extends BaseComponent
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return View::file(__DIR__.'/small-component.blade.php');
    }
}
