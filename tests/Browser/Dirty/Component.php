<?php

namespace Tests\Browser\Dirty;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = '';
    public $bar = '';
    public $baz = '';
    public $bob = '';
    public $ted = '';

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
