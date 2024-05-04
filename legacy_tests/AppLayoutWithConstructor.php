<?php

namespace LegacyTests;

use Illuminate\View\Component;

class AppLayoutWithConstructor extends Component
{
    public function __construct(public $foo = 'bar') {}

    public function render()
    {
        return view('layouts.app-from-class-component');
    }
}
