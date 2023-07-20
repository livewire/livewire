<?php

namespace LegacyTests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class DirtyDataComponent extends BaseComponent
{
    protected $queryString = ['page' => ['history' => true]];

    public $page = 1;
    public $foo = ['bar' => ''];

    public function nextPage()
    {
        $this->page++;
    }

    public function render()
    {
        return View::file(__DIR__.'/dirty-data.blade.php');
    }
}
