<?php

namespace Tests\Browser\Alpine;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $count = 0;

    public function setCount($value)
    {
        $this->count = $value;
    }

    public function dispatchSomeEvent()
    {
        $this->dispatchBrowserEvent('some-event', 'bar');
    }

    public function returnValue($value)
    {
        return $value;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
