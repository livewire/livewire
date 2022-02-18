<?php

namespace Tests;

use Illuminate\View\Component;

class AppLayoutWithConstructor extends Component
{
    public $foo;

    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function render()
    {
        return view('layouts.app-from-class-component');
    }
}
