<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ComponentWithMount extends BaseComponent
{
    public $renamedCompletely;

    public function mount($id)
    {
        $this->renamedCompletely = $id;
    }

    public function changeId()
    {
        $this->renamedCompletely = 5;
    }

    public function render()
    {
        return View::file(__DIR__.'/component-with-mount.blade.php');
    }
}
