<?php

namespace LegacyTests\Browser\Events;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ListenerComponent extends BaseComponent
{
    public $message = '';

    public function render()
    {
        return View::file(__DIR__ . '/listener-view.blade.php');
    }
}
