<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $step;
    public $showHelp = false;

    protected $queryString = ['showHelp'];

    public function mount(Step $step)
    {
        $this->step = $step;
    }

    public function setStep($id)
    {
        $this->step = Step::findOrFail($id);
    }

    public function toggleHelp()
    {
        $this->showHelp = ! $this->showHelp;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php')->with(['id' => $this->id]);
    }
}
