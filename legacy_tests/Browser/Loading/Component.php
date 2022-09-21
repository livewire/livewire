<?php

namespace LegacyTests\Browser\Loading;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $baz = '';

    public $bob = ['name' => ''];

    public function hydrate()
    {
        usleep(1000 * 250);
    }

    public function throwError()
    {
        throw new \Exception;
    }

    public function foo() {}
    public function bar() {}

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
