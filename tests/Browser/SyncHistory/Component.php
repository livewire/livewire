<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public Model $parent;

    public Model $child;

    public $toggle;

    public $showNestedComponent = false;

    protected $queryString = [
        'toggle' => ['except' => null]
    ];

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
