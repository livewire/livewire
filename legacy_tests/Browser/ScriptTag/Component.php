<?php

namespace LegacyTests\Browser\ScriptTag;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $withScript = false;

    public function show()
    {
        $this->withScript = true;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
