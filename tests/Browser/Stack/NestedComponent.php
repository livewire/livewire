<?php

namespace Tests\Browser\Stack;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class NestedComponent extends BaseComponent
{
    public $bob = 'bob';

    public function updatedText($value)
    {
        $this->bob = $value . '1';
    }

    public function render()
    {
        return View::file(__DIR__.'/view-nested.blade.php');
    }
}
