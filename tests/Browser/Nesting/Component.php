<?php

namespace Tests\Browser\Nesting;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    protected $queryString = ['showChild'];

    public $showChild = false;
    public $key = 'foo';

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
