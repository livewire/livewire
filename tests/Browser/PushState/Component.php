<?php

namespace Tests\Browser\PushState;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = 'bar';
    public $bar = 'baz';

    public $showNestedComponent = false;

    protected $queryString = [
        'foo',
        'bar' => ['except' => 'except-value']
    ];

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
