<?php

namespace Tests\Browser\SupportStringables;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $string;

    public function mount()
    {
        $this->string = Str::of('Be excellent to each other');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
