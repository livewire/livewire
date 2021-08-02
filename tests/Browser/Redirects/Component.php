<?php

namespace Tests\Browser\Redirects;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $message = 'foo';
    public $foo;

    protected $rules = [
        'foo.name' => '',
    ];

    public function mount()
    {
        $this->foo = Foo::first();
    }

    public function flashMessage()
    {
        session()->flash('message', 'some-message');
    }

    public function redirectWithFlash()
    {
        session()->flash('message', 'some-message');

        return $this->redirect('/livewire-dusk/Tests%5CBrowser%5CRedirects%5CComponent');
    }

    public function redirectPage()
    {
        $this->message = 'bar';
        $this->foo->update(['name' => $this->foo->name == 'foo2' ? 'bar2' : 'foo2']);

        return $this->redirect('/livewire-dusk/Tests%5CBrowser%5CRedirects%5CComponent?abc');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
