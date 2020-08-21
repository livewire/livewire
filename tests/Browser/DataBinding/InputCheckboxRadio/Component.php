<?php

namespace Tests\Browser\DataBinding\InputCheckboxRadio;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = true;
    public $bar = ['b'];
    public $baz = 2;

    public function updateFooTo($value)
    {
        $this->foo = $value;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
