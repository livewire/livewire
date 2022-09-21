<?php

namespace LegacyTests\Browser\Headers;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $output = '';
    public $altoutput = '';

    public function setOutputToFooHeader()
    {
        $this->output = request()->header('x-foo-header', '');
        $this->altoutput = request()->header('x-bazz-header', '');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
