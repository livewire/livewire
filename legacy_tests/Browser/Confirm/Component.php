<?php

namespace LegacyTests\Browser\Confirm;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public ?string $data = null;

    public function dummyAction()
    {
        $this->data = 'confirmed';
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
