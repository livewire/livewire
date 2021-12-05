<?php

namespace Tests\Browser\Morphdom;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class NestedComponent extends BaseComponent
{
    public $output = '';

    public function render()
    {
        return View::file(__DIR__.'/view-nested.blade.php');
    }
}
