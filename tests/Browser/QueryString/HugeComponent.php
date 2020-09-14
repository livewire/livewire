<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class HugeComponent extends BaseComponent
{
    protected $queryString = ['count'];

    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return View::file(__DIR__.'/huge-component.blade.php');
    }
}
