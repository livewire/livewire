<?php

namespace LegacyTests\Browser\Confirm;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public ?string $confirmData = null;
    public ?string $promptData = null;

    public function confirmAction()
    {
        $this->confirmData = 'confirmed';
    }

    public function promptAction()
    {
        $this->promptData = 'confirmed';
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
