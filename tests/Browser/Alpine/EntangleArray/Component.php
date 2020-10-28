<?php

namespace Tests\Browser\Alpine\EntangleArray;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $livewireList = [1,2,3,4];

    public $livewireSearch;

    public function updatedLivewireSearch()
    {
        $this->change();
    }

    public function change()
    {
        $this->livewireList = [5,6,7,8];
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
