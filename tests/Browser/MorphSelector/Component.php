<?php

namespace Tests\Browser\MorphSelector;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function click($target, $text)
    {
        $this->morphSelector($target, $text);
    }

    public function clickBoth()
    {
        $this->morphSelector('#result1', 'Foo');
        $this->morphSelector('#result2', 'Bar');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
