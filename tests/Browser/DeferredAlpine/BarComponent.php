<?php

namespace Tests\Browser\DeferredAlpine;

use Livewire\Component as BaseComponent;

class BarComponent extends BaseComponent
{
    public $bar = 1;
    public function incrementBar()
    {
        $this->bar ++;
    }

    public function render()
    {
        return view()->file(__DIR__ . '/bar.blade.php')
            ->layout('components.layouts.app-for-deferred-alpine');
    }
}
