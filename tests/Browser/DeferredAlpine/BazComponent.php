<?php

namespace Tests\Browser\DeferredAlpine;

use Livewire\Component as BaseComponent;

class BazComponent extends BaseComponent
{
    public $baz = 10;

    public function render()
    {
        return view()->file(__DIR__ . '/baz.blade.php')
            ->layout('components.layouts.app-for-deferred-alpine');
    }
}
