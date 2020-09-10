<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class TypeComponent extends BaseComponent
{
    public $type;
    protected $queryString = ['type'];

    public function render()
    {
        return View::file(__DIR__.'/type.blade.php');
    }
}
