<?php

namespace Tests\Browser\SupportCollections;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $things;

    public function mount()
    {
        $this->things = collect('foo');
    }

    public function addBar()
    {
        $this->things->push('bar');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
