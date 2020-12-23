<?php

namespace Tests\Browser\DataBinding\InputSelect;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $single;
    public $singleValue;
    public $singleNumber = 3;
    public $placeholder = '';
    public $multiple = [];

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
