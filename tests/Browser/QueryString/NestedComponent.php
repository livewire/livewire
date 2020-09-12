<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class NestedComponent extends BaseComponent
{
    public $baz = 'bop';

    protected $queryString = ['baz'];

    public function render()
    {
        return View::file(__DIR__.'/nested-view.blade.php');
    }
}
