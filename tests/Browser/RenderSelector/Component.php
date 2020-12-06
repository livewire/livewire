<?php

namespace Tests\Browser\RenderSelector;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function click($id)
    {
        $this->renderSelector('#' . $id, 'Clicked');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
