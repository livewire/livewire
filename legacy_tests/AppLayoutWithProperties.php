<?php

namespace LegacyTests;

use Illuminate\View\Component;

class AppLayoutWithProperties extends Component
{
    public function render()
    {
        return view('layouts.app-from-class-component-with-properties', [
            'foo' => 'bar',
        ]);
    }
}
