<?php

namespace LegacyTests\Browser\Redirects;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $message = 'foo';
    public $foo = 'hey';

    public $disableBackButtonCache = true;

    protected $queryString = [
        'disableBackButtonCache',
    ];

    protected $rules = [
        'foo.name' => '',
    ];

    public function mount()
    {
        // $this->foo = Foo::first();

        // Set "disable back button cache" flag based off of query string
        // $this->disableBackButtonCache ? Livewire::disableBackButtonCache() : Livewire::enableBackButtonCache();
    }

    public function flashMessage()
    {
        session()->flash('message', 'some-message');
    }

    public function redirectWithFlash()
    {
        session()->flash('message', 'some-message');

        return $this->redirect('/livewire-dusk/LegacyTests%5CBrowser%5CRedirects%5CComponent');
    }

    public function redirectPage()
    {
        $this->message = 'bar';

        return $this->redirect('/livewire-dusk/LegacyTests%5CBrowser%5CRedirects%5CComponent?abc');
    }

    public function redirectPageWithModel()
    {
        // $this->foo->update(['name' => 'bar']);
        $this->foo = 'bar';

        return $this->redirect('/livewire-dusk/Tests%5CBrowser%5CRedirects%5CComponent?abc&disableBackButtonCache='. ($this->disableBackButtonCache ? 'true' : 'false'));
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
