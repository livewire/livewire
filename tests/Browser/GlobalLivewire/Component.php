<?php

namespace Tests\Browser\GlobalLivewire;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $output = '';

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
