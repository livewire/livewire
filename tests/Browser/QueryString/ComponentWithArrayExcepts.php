<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ComponentWithArrayExcepts extends BaseComponent
{
    protected $queryString = [
        'search' => ['except' => ['black', 'white']],
    ];

    public $search = '';

    public function render()
    {
        return View::file(__DIR__.'/array-excepts.blade.php');
    }
}

