<?php


namespace Tests\Browser\Diffing;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $page = 2;

    public function toggle()
    {
        $this->page = $this->page === 1 ? 2 : 1;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}