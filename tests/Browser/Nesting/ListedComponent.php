<?php

namespace Tests\Browser\Nesting;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ListedComponent extends BaseComponent
{
    public function render()
    {
        return View::file(__DIR__ . '/view-listed.blade.php');
    }
}
