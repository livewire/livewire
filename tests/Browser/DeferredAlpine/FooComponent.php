<?php

namespace Tests\Browser\DeferredAlpine;

use Livewire\Component as BaseComponent;

class FooComponent extends BaseComponent
{
    public $foo = 'Hello Alpine';

    public function render()
    {
        return view()->file(__DIR__ . '/foo.blade.php')
            ->layout('components.layouts.app-for-deferred-alpine');
    }
}
