<?php

namespace Tests\Browser\Redirects;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $message = 'foo';
    public $foo;

    public $shouldSkipRenderOnRedirect = true;
    public $disableBackButtonCache = true;

    protected $queryString = [
        'shouldSkipRenderOnRedirect',
        'disableBackButtonCache',
    ];

    protected $rules = [
        'foo.name' => '',
    ];

    public function mount()
    {
        $this->foo = Foo::first();

        $this->disableBackButtonCache ? $this->disableBackButtonCache() : $this->enableBackButtonCache();
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

        config()->set('livewire.should_skip_render_on_redirect', $this->shouldSkipRenderOnRedirect);

        return $this->redirect('/livewire-dusk/Tests%5CBrowser%5CRedirects%5CComponent?abc');
    }

    public function redirectPageWithModel()
    {
        $this->foo->update(['name' => 'bar']);

        // overriding config here like for skip render won't work as the config value is loaded in the constructor
        // config()->set('livewire.disable_back_button_cache', $this->disableBackButtonCache);

        return $this->redirect('/livewire-dusk/Tests%5CBrowser%5CRedirects%5CComponent?abc');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
