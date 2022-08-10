<?php

namespace Tests\Browser\Events;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class NestedComponentA extends BaseComponent
{
    protected $listeners = ['foo', 'bar'];

    public $lastEvent = '';

    public $lastBarEvent = '';

    public function foo($value)
    {
        $this->lastEvent = $value;
    }

    public function bar($value)
    {
        $this->lastBarEvent = $value;
    }

    public function render()
    {
        return View::file(__DIR__.'/nested-a.blade.php');
    }
}
