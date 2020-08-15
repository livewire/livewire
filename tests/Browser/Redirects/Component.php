<?php

namespace Tests\Browser\Redirects;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function flashMessage()
    {
        session()->flash('message', 'some-message');
    }

    public function redirectWithFlash()
    {
        session()->flash('message', 'some-message');

        return $this->redirect('/livewire-dusk/Tests%5CBrowser%5CRedirects%5CComponent');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
