<?php

namespace Tests\Browser\Morphdom;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = false;
    public $bar = false;
    public $baz = false;
    public $bob = false;
    public $lob = false;
    public $law = false;
    public $qux = false;

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
