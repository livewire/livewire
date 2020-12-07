<?php

namespace Tests\Browser\MorphSelector;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function click($target)
    {
        $this->morphSelector($target, 'Clicked');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
