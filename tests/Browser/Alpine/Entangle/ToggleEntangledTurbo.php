<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class ToggleEntangledTurbo extends BaseComponent
{
    public $active = false;
    public $title = 'Showing Livewire&Alpine Component after a Turbo Visit';

    public function render()
    {
        return view()->file(__DIR__ . '/view-toggle-entangled-turbo.blade.php')
            ->layout('components.layouts.app-for-turbo-views');
    }
}
