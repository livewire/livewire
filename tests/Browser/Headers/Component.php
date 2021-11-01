<?php

namespace Tests\Browser\Headers;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $output = '';

    public function setOutputToFooHeader()
    {
        $this->output = request()->header('x-foo-header', '');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
