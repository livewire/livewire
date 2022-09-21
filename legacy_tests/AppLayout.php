<?php

namespace LegacyTests;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public $foo = 'bar';

    public function render()
    {
        return view('layouts.app-from-class-component');
    }
}
