<?php

namespace Tests\Browser\Alpine;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $count = 0;
    public $special = 'abc';
    public $zorp = 'before';

    public $nested = [
        'count' => 0,
    ];

    public function incrementNestedCount()
    {
        $this->nested['count'] = $this->nested['count'] + 1;
    }

    public function setCount($value)
    {
        $this->count = $value;
    }

    public function setSpecial($value)
    {
        $this->special = $value;
    }

    public function dispatchSomeEvent()
    {
        $this->dispatchBrowserEvent('some-event', 'bar');
    }

    public function returnValue($value)
    {
        return $value;
    }

    public function updatingCount()
    {
        if ($this->count === 100) throw new \Exception('"count" shouldnt already be "100". This means @entangle made an extra request after Livewire set the data.');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
