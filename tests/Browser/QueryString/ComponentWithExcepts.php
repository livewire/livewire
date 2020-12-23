<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ComponentWithExcepts extends BaseComponent
{
    public $page = 1;

    protected $queryString = ['page' => ['except' => 1]];

    public function render()
    {
        return View::file(__DIR__.'/excepts.blade.php');
    }
}
