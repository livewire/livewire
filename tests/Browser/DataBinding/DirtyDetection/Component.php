<?php

namespace Tests\Browser\DataBinding\DirtyDetection;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = 'initial';
    public $bar = [];

    public function changeFoo()
    {
        $this->foo = 'changed';
    }

    public function resetBar()
    {
        $this->reset('bar');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
