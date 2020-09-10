<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ChildComponent extends BaseComponent
{
    public $darkmode = false;
    protected $queryString = ['darkmode'];

    public function toggleDarkmode()
    {
        $this->darkmode = ! $this->darkmode;
    }

    public function render()
    {
        return View::file(__DIR__.'/child.blade.php')->with(['id' => $this->id]);
    }
}
